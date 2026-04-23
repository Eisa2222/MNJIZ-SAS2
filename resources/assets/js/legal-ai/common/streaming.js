/**
 * common/streaming.js
 * ============================================================================
 * وحدة مشتركة ومتخصصة في معالجة تدفق البيانات (SSE) من الخادم.
 *
 * المسؤوليات:
 * 1.  إرسال طلب البث إلى الخادم.
 * 2.  قراءة البيانات المتدفقة (chunks) وتمريرها للمحلل المناسب.
 * 3.  تحديث واجهة المستخدم في الوقت الفعلي مع النص المستلم.
 * 4.  حفظ الرد الكامل في قاعدة البيانات بعد انتهاء البث.
 * ============================================================================
 */

import * as ui from './ui.js';
import DOMPurify from 'dompurify';
import { marked } from 'marked';

// إعداد مكتبة marked للتعامل مع فواصل الأسطر بشكل صحيح
marked.setOptions({
    breaks: true,
    gfm: true,
});

// -----------------------------------------------------------------------------
// محللات بيانات البث (Streaming Data Parsers)
// -----------------------------------------------------------------------------

/**
 * محلل متخصص لبيانات OpenAI المتدفقة.
 * @param {string} rawChunk - السطر الخام من بيانات البث.
 * @returns {string|null} النص المستخرج أو null.
 */
function parseOpenAiChunk(rawChunk) {
    if (rawChunk.startsWith('data: ')) {
        const content = rawChunk.substring(6);
        if (content.trim() === '[DONE]') {
            return null; // إشارة انتهاء البث من OpenAI
        }
        try {
            const data = JSON.parse(content);
            return data.choices?.[0]?.delta?.content || '';
        } catch (e) {
            // تجاهل أخطاء التحويل إذا كان الـ JSON غير مكتمل
            return null;
        }
    }
    return null;
}

/**
 * محلل متخصص لبيانات Claude (Anthropic) المتدفقة.
 * @param {string} rawChunk - السطر الخام من بيانات البث.
 * @returns {string|null} النص المستخرج أو null.
 */
function parseClaudeChunk(rawChunk) {
    const dataLine = rawChunk.split('\n').find(line => line.startsWith('data: '));
    if (dataLine) {
        try {
            const data = JSON.parse(dataLine.substring(6));
            if (data.type === 'content_block_delta' && data.delta?.type === 'text_delta') {
                return data.delta.text;
            }
        } catch (e) {
            return null;
        }
    }
    return null;
}

/**
 * محلل متخصص لبيانات Gemini (Google AI) المتدفقة.
 * @param {string} rawChunk - السطر الخام من بيانات البث.
 * @returns {string|null} النص المستخرج أو null.
 */
function parseGeminiChunk(rawChunk) {
    if (rawChunk.startsWith('data: ')) {
        const content = rawChunk.substring(6);
        try {
            const data = JSON.parse(content);
            return data.candidates?.[0]?.content?.parts?.[0]?.text || '';
        } catch (e) {
            return null;
        }
    }
    return null;
}

/**
 * دالة محورية تختار المحلل المناسب بناءً على المزود النشط.
 * @param {string} rawChunk - السطر الخام من البيانات.
 * @param {string} provider - اسم المزود النشط ('openai', 'claude', 'gemini').
 * @returns {string|null}
 */
function parseChunk(rawChunk, provider) {
    switch (provider) {
        case 'claude':
            return parseClaudeChunk(rawChunk);
        case 'gemini':
            return parseGeminiChunk(rawChunk);
        case 'openai':
        default:
            return parseOpenAiChunk(rawChunk);
    }
}

// -----------------------------------------------------------------------------
// دوال مساعدة لإدارة الـ Typing Indicator بشكل احترافي
// -----------------------------------------------------------------------------

/**
 * إخفاء مؤشر الكتابة بتأثير سلس
 * @param {HTMLElement} typingIndicator - عنصر مؤشر الكتابة
 * @returns {Promise} يكتمل عند انتهاء الانتقال
 */
function hideTypingIndicatorSmoothly(typingIndicator) {
    return new Promise(resolve => {
        if (!typingIndicator) {
            resolve();
            return;
        }

        // إضافة تأثير الشفافية
        typingIndicator.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
        typingIndicator.style.opacity = '0';
        typingIndicator.style.transform = 'scale(0.8)';

        // الانتظار حتى انتهاء التأثير ثم إزالة العنصر
        setTimeout(() => {
            if (typingIndicator && typingIndicator.parentNode) {
                typingIndicator.remove();
            }
            resolve();
        }, 300);
    });
}

/**
 * إظهار محتوى الرسالة بتأثير سلس
 * @param {HTMLElement} aiResponseContent - عنصر محتوى الرسالة
 */
function showResponseContentSmoothly(aiResponseContent) {
    if (!aiResponseContent) return;

    aiResponseContent.style.display = 'block';
    aiResponseContent.style.opacity = '0';
    aiResponseContent.style.transform = 'translateY(10px)';
    aiResponseContent.style.transition = 'opacity 0.4s ease-out, transform 0.4s ease-out';

    // تطبيق التأثير
    requestAnimationFrame(() => {
        aiResponseContent.style.opacity = '1';
        aiResponseContent.style.transform = 'translateY(0)';
    });
}

/**
 * إدارة التبديل بين مؤشر الكتابة ومحتوى الرسالة
 * @param {HTMLElement} typingIndicator - مؤشر الكتابة
 * @param {HTMLElement} aiResponseContent - محتوى الرسالة
 * @returns {Promise} يكتمل عند انتهاء التبديل
 */
async function transitionToResponseContent(typingIndicator, aiResponseContent) {
    // إخفاء مؤشر الكتابة أولاً
    await hideTypingIndicatorSmoothly(typingIndicator);

    // ثم إظهار محتوى الرسالة
    showResponseContentSmoothly(aiResponseContent);
}

// -----------------------------------------------------------------------------
// دالة معالجة البث الرئيسية المحسنة
// -----------------------------------------------------------------------------

/**
 * تعالج عملية البث الكاملة من إرسال الطلب إلى تحديث الواجهة.
 *
 * @param {string} url - عنوان URL لنقطة نهاية البث.
 * @param {object} options - خيارات طلب fetch (method, body, headers).
 * @param {string} aiMessageId - المعرف المؤقت لعنصر رسالة الـ AI في الـ DOM.
 * @param {object} STATE - كائن الحالة العام للتطبيق.
 * @returns {Promise<string>} النص الكامل للرد بعد انتهاء البث.
 */
export async function processStream(url, options, aiMessageId, STATE) {
    // 1. إرسال الطلب الأولي
    let response;
    try {
        response = await fetch(url, options);
    } catch (networkError) {
        throw new Error('فشل الاتصال بالشبكة. يرجى التحقق من اتصالك بالإنترنت.');
    }

    if (!response.ok || !response.body) {
        const errorText = await response.text();
        try {
            const errorJson = JSON.parse(errorText.replace('error: ', ''));
            throw new Error(errorJson.message || `خطأ من الخادم: ${response.statusText}`);
        } catch (e) {
            throw new Error(`خطأ من الخادم: ${errorText || response.statusText}`);
        }
    }

    // 2. إعداد قارئ البث
    const reader = response.body.getReader();
    const decoder = new TextDecoder("utf-8");
    const aiMessageContainer = document.getElementById(aiMessageId);

    if (!aiMessageContainer) {
        return '';
    }

    const aiResponseContent = aiMessageContainer.querySelector('.ai-response-content');
    const actionsFooter = aiMessageContainer.querySelector('.chat-message-actions');
    const typingIndicator = aiMessageContainer.querySelector('.ai-typing-indicator');

    let fullAiResponseText = '';
    let isStreamStarted = false;
    let isTransitionCompleted = false;
    let buffer = '';
    let isUpdatePending = false;
    let updateQueue = [];

    // دالة تحديث محتوى الـ DOM مع إدارة الطابور
    const updateDOM = () => {
        if (aiResponseContent && isTransitionCompleted) {
            const latestContent = updateQueue.length > 0 ? updateQueue[updateQueue.length - 1] : fullAiResponseText;
            aiResponseContent.innerHTML = DOMPurify.sanitize(marked.parse(latestContent));
            ui.scrollToBottom();
            updateQueue = []; // تنظيف الطابور
        }
        isUpdatePending = false;
    };

    // دالة جدولة التحديث
    const scheduleUpdate = (content) => {
        updateQueue.push(content);
        if (!isUpdatePending) {
            isUpdatePending = true;
            if (isTransitionCompleted) {
                requestAnimationFrame(updateDOM);
            }
        }
    };

    // معالجة القراءة من البث
    while (true) {
        const { value, done } = await reader.read();
        if (done) {
            break;
        }

        buffer += decoder.decode(value, { stream: true });
        const lines = buffer.split('\n');
        buffer = lines.pop() || '';

        for (const line of lines) {
            if (line.trim() === '') continue;

            const textContent = parseChunk(line, STATE.activeProvider);
            if (textContent !== null && textContent !== '') {
                fullAiResponseText += textContent;

                // التحقق من بداية البث وإجراء التبديل
                if (!isStreamStarted) {
                    isStreamStarted = true;

                    // إجراء التبديل السلس من مؤشر الكتابة إلى المحتوى
                    await transitionToResponseContent(typingIndicator, aiResponseContent);
                    isTransitionCompleted = true;

                    // تحديث المحتوى الأولي
                    if (updateQueue.length > 0 || fullAiResponseText) {
                        updateDOM();
                    }
                }

                // جدولة التحديثات للمحتوى الجديد
                if (isTransitionCompleted) {
                    scheduleUpdate(fullAiResponseText);
                }
            }
        }
    }

    // التأكد من إزالة مؤشر الكتابة في حالة عدم وجود محتوى
    if (!isStreamStarted && typingIndicator) {
        await hideTypingIndicatorSmoothly(typingIndicator);
        if (aiResponseContent) {
            aiResponseContent.style.display = 'block';
        }
    }

    // التحديث النهائي للمحتوى
    if (isTransitionCompleted || !isStreamStarted) {
        updateDOM();
    }

    // إظهار أزرار الإجراءات بتأثير سلس
    if (fullAiResponseText.trim() && actionsFooter) {
        actionsFooter.style.display = 'flex';
        actionsFooter.style.opacity = '0';
        actionsFooter.style.transition = 'opacity 0.3s ease-in';

        setTimeout(() => {
            actionsFooter.style.opacity = '1';
        }, 100);
    }

    return fullAiResponseText;
}

// -----------------------------------------------------------------------------
// دالة حفظ الرد في قاعدة البيانات
// -----------------------------------------------------------------------------

/**
 * يرسل رد الذكاء الاصطناعي الكامل إلى الخادم لحفظه في قاعدة البيانات.
 *
 * @param {string} chatId - معرف المحادثة.
 * @param {string} messageText - النص الكامل لرسالة الـ AI.
 * @param {object} STATE - كائن الحالة العام للتطبيق.
 * @returns {Promise<object|null>} بيانات الرسالة المحفوظة أو null عند الفشل.
 */
export async function saveAiResponseToDatabase(chatId, messageText, STATE) {
    const saveRoute = STATE.routes.saveResponse.replace('__CHAT_ID__', chatId);
    try {
        const response = await fetch(saveRoute, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': STATE.csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ message: messageText })
        });
        if (!response.ok) {
            return null;
        }
        return await response.json();
    } catch (error) {
        return null;
    }
}
