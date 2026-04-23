/**
 * Chat User Auto Selector - Standalone Module
 * يعمل بشكل مستقل تماماً دون الاعتماد على أي كود آخر
 */

'use strict';

(function () {
    console.log('🚀 Chat User Selector module loaded');

    // انتظار تحميل DOM و jQuery
    function waitForDependencies(callback) {
        if (typeof $ !== 'undefined' && document.readyState === 'complete') {
            callback();
        } else {
            setTimeout(() => waitForDependencies(callback), 50);
        }
    }

    // الدالة الرئيسية لتحديد المستخدم
    function initializeUserSelector() {
        console.log('🔧 Initializing Chat User Selector');

        // انتظار تحميل المتغيرات والعناصر
        function checkAndExecute() {
            // فحص وجود المتغيرات المطلوبة
            if (typeof window.selectedUserId === 'undefined') {
                console.log('⏳ Waiting for window.selectedUserId...');
                setTimeout(checkAndExecute, 100);
                return;
            }

            // فحص وجود عناصر المستخدمين في DOM
            const userElements = $('.chat-contact-list-item.user');
            if (userElements.length === 0) {
                console.log('⏳ Waiting for user elements...');
                setTimeout(checkAndExecute, 100);
                return;
            }

            console.log('✅ All dependencies ready, starting user selection');
            executeUserSelection();
        }

        // تنفيذ منطق اختيار المستخدم
        function executeUserSelection() {
            console.log('🎯 Starting user selection logic');
            console.log('📊 Available users count:', $('.chat-contact-list-item.user').length);
            console.log('🔍 Target user ID:', window.selectedUserId);

            // طباعة جميع المستخدمين المتاحين
            $('.chat-contact-list-item.user').each(function (index) {
                const userId = $(this).data('id');
                const userName = $(this).find('.chat-contact-name').text().trim();
                console.log(`👤 User ${index}: ID=${userId}, Name='${userName}'`);
            });

            // فحص وجود مستخدم محدد مسبقاً
            if (window.selectedUserId &&
                window.selectedUserId !== null &&
                window.selectedUserId !== 'null' &&
                window.selectedUserId !== '') {
                console.log('🎯 Pre-selection detected for user ID:', window.selectedUserId);

                // البحث عن المستخدم المحدد
                const targetUser = $(`.chat-contact-list-item.user[data-id="${window.selectedUserId}"]`);
                console.log('🔍 Target user found:', targetUser.length > 0);

                if (targetUser.length > 0) {
                    console.log('✅ Target user exists, proceeding with selection');

                    // تمرير سلس للمستخدم المحدد
                    if (targetUser[0].scrollIntoView) {
                        targetUser[0].scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }

                    // تحديد المستخدم مع تأخير بسيط
                    setTimeout(() => {
                        console.log('🎯 Triggering click on target user');
                        targetUser.trigger('click');

                        // إضافة تمييز بصري
                        targetUser.addClass('pre-selected-user');

                        // إزالة التمييز بعد 3 ثوان
                        setTimeout(() => {
                            targetUser.removeClass('pre-selected-user');
                        }, 3000);

                        console.log('✅ Pre-selection completed successfully');
                    }, 200);

                    return; // إنهاء الدالة هنا
                } else {
                    console.log('❌ Target user not found in DOM');
                }
            } else {
                console.log('📝 No pre-selection found, using default behavior');
            }

            // السلوك الافتراضي: اختيار المستخدم الأول
            console.log('🔄 Falling back to first user selection');
            const firstUser = $('.chat-contact-list-item.user').first();
            if (firstUser.length > 0) {
                const firstUserId = firstUser.data('id');
                console.log('🔄 Selecting first user with ID:', firstUserId);
                firstUser.trigger('click');
                console.log('✅ First user selected as fallback');
            } else {
                console.log('❌ No users found in DOM');
            }
        }

        // بدء عملية الفحص والتنفيذ
        checkAndExecute();
    }

    // انتظار تحميل كل شيء ثم البدء
    waitForDependencies(() => {
        console.log('🔧 Dependencies loaded, starting user selector');

        // انتظار إضافي للتأكد من تحميل كل شيء
        setTimeout(() => {
            initializeUserSelector();
        }, 150);
    });
})();
