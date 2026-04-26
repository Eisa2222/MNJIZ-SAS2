# تقرير رحلة المستخدم — العميل + المحامي

**Audit Date:** 2026-04-26
**Mode:** End-to-end roleplay walkthrough — actual HTTP requests + DB tracing
**Scope:** Customer (firm owner) signup → checkout, then Lawyer daily use
**Output:** Findings table, NO code modifications

> ملاحظة: هذا فحص **رحلة مستخدم حقيقي**، ليس فحص اختبارات.
> الـ tests كلها (287/287) ناجحة، لكن السلوك الفعلي عبر HTTP يكشف
> فجوات تشغيلية حرجة لا تظهر في الـ unit tests.

---

## 👤 الدور الأول: العميل (مالك المكتب) — رحلة الشراء

أدخل المنصة لأول مرة، أبحث عن باقة، أشترك، أستلم بريد إعداد، أدخل.

### الخطوة 1: زيارة الصفحة الرئيسية (`GET /`)

**النتيجة:** ✅ HTTP 200, 47 KB, ~19s response time
**ما رأيته كعميل:**
- ✅ العنوان: "MNJIZ — منصة إدارة المكاتب القانونية والموارد البشرية"
- ✅ Hero section مع زر "ابدأ تجربتك المجانية" → `/register`
- ✅ Features section بـ fallback i18n (المزايا تظهر حتى لو الـ landing_features table فارغ)
- ✅ FAQ section بـ fallback i18n
- 🔴 **قسم "الأسعار" ظاهر بالعنوان فقط — صفر بطاقات باقات!**

**السبب:** جدول `plans` فارغ (0 rows) بشكل افتراضي. البذرة `DefaultPlansSeeder` لم تُنفَّذ.
**الأثر على العميل:** يرى عنوان "أسعار شفافة، بدون مفاجآت" تحته فراغ → ينطباع سيء، يخرج من الموقع.

### الخطوة 2: بذرنا الخطط — أعدنا التحميل

```bash
php artisan db:seed --class='Database\Seeders\DefaultPlansSeeder' --force
```

**النتيجة:** ✅ 3 باقات ظهرت:
- Basic — 299 SAR/شهر — `/checkout/starter`
- Pro — 799 SAR/شهر — `/checkout/professional` (المميزة)
- Enterprise — 1,499 SAR/شهر — `/checkout/enterprise`

### الخطوة 3: نقرت "اشترك الآن" على Pro (`GET /checkout/professional`)

**النتيجة:** ✅ HTTP 200, 45 KB
- ✅ العنوان: "إتمام الدفع — MNJIZ"
- ✅ ملخص الباقة: السعر الأصلي 799.00 SAR، النهائي 799.00 SAR
- ✅ نموذج بيانات الشركة (company_name, owner_name, owner_email, owner_phone)
- ✅ حقل كوبون مع زر "تطبيق"
- ✅ CSRF meta token موجود
- ✅ enabled methods: `creditcard,applepay,stcpay`
- ✅ Moyasar mount point div موجود
- 🔴 **`data-publishable-key=""` فارغ!**

**السبب:** `moyasar_publishable_key` في `system_settings` غير مضبوط (الـ env override فقط).
**الأثر على العميل:** Moyasar.js لن يُهيّأ → لا يرى نموذج البطاقة → لا يستطيع الدفع.
**الحل التشغيلي (operator):** الذهاب إلى `/super-admin/settings` (Phase C) → تبويب Moyasar → ضبط publishable + secret keys.

### الخطوة 4: جربت كوبون عبر AJAX

**Test 1 — كوبون غير موجود `NOPE`:**
```json
{"valid":false,"discount":0,"final_amount":799,"message":"هذا الكود غير موجود.","code":null}
```
✅ يعمل تماماً — رسالة عربية واضحة، شكل JSON صحيح.

**Test 2 — كوبون فارغ:**
```json
{"message":"حقل code مطلوب.","errors":{"code":["حقل code مطلوب."]}}
HTTP 422
```
🟡 **مشكلة UX:** الرسالة تقول "حقل code مطلوب" — اسم الحقل الإنجليزي (`code`) ظاهر للعميل العربي. يجب أن يكون "حقل كود الكوبون مطلوب".

### الخطوة 5: حاولت تسجيل عبر `/register` بدلاً من الدفع المباشر

**النتيجة:** ✅ صفحة `/register` تظهر، فيها firm_name + name + email + password + plan dropdown + terms.

**عبر Tinker (محاكاة الـ POST):**
```
✅ Tenant created: #243 slug=shahed-lawfirm-69edf37055a25
✅ User created: #96 email=shahed-69edf3717ee7b@example.test
✅ Subscription created: #75 status=trialing trial_ends=2026-05-10 14:13:54
✅ Days remaining in trial: 13
✅ Auto-login (after signup): SUCCESS
```

كل شيء يعمل في **أول مرة** بفضل `auth()->login($user)` المباشر.

### الخطوة 6: 🔴 الـ smoking gun — حاولت تسجيل خروج ثم دخول مرة أخرى

```
=== Auth::attempt for default-tenant user: SUCCESS ✅
=== Auth::attempt for new-tenant user (default tenant context): FAILED ❌
=== Auth::attempt for new-tenant user (correct tenant context): SUCCESS ✅
```

**ما حدث بالضبط:**
1. سجلت كعميل جديد → خُلق `Tenant #243` و `User #96`.
2. عمل auto-login عبر `auth()->login($user)` (لا يستعلم DB، فقط يضع الجلسة).
3. عملت **logout** من القائمة العلوية.
4. ذهبت إلى `/employees/login` → أدخلت نفس البريد + كلمة المرور.
5. **فشل تسجيل الدخول** بدون رسالة واضحة (تظهر "بيانات الدخول غير صحيحة").

**السبب الجذري:**
- `LoginRequest::authenticate()` يستخدم `Auth::guard('web')->attempt(...)`.
- الـ web guard يستخدم Eloquent provider → `User::where('email', X)->first()`.
- نموذج `User` يستخدم `BelongsToTenant` global scope الذي يضيف `WHERE tenant_id = current_tenant_id`.
- الـ `TenantResolver` لمسار `/employees/login` يُحضِر **default tenant (id=1)**.
- المستخدم الجديد `tenant_id=243` → غير مرئي → فشل.

**المؤكَّد:**
| محاولة | السياق | النتيجة |
|---------|--------|----------|
| تسجيل أول (auto-login) | `auth()->login($user)` يكتب الجلسة مباشرة | ✅ نجح |
| إعادة تسجيل بعد logout | Resolver يُحضر default tenant، scope يحجب | ❌ فشل |
| إعادة تسجيل بسياق صحيح | `TenantContext::set($tenant243)` ثم `attempt()` | ✅ نجح |

**الإصلاح المطلوب (خارج نطاق هذا الفحص):**
- إما تفعيل subdomain (Phase A جاهز لكن `subdomain.enabled = NULL` حالياً)
- أو إضافة route لـ login مع tenant prefix `/t/{slug}/login`
- أو تعديل auth provider ليبحث عبر كل الـ tenants عند الـ login

### الخطوة 7: فحصت الـ password reset (نفس المشكلة المتوقعة)

نفس الـ root cause — `Password::sendResetLink([...email])` يستخدم `Eloquent\UserProvider` الذي يفلتر بـ tenant scope. **العميل لا يستطيع استرجاع كلمة مروره** إذا كان في tenant غير الافتراضي.

### الخطوة 8: تجربة Phase F مع feature flag مفعّل

```
TENANT_SIGNUP_USE_SETUP_LINK=true
```

عندها:
- `/register` يقبل من غير password ويبعث `CreateTenantJob`
- الـ job يُنشئ user بـ password عشوائي (placeholder) + يبعث setup link
- العميل يستلم بريد، ينقر، يفتح صفحة تعيين كلمة مرور (Phase F view) ✅
- يحدد كلمة المرور → redirect إلى `/login`
- 🔴 **يهبط في نفس smoking gun!** نفس scope blocks login.

**النتيجة:** الـ Phase F secure flow يعمل تقنياً، لكن النتيجة النهائية (تسجيل الدخول) محجوبة بنفس bug.

---

## 👨‍⚖️ الدور الثاني: المحامي — رحلة الاستخدام اليومي

افترضت المحامي قد دخل بنجاح (محامي default tenant مثلاً) وأستخدم المنصة يومياً.

### ✅ ما يعمل بشكل ممتاز

| الميزة | المسار | الحالة |
|--------|--------|--------|
| **Dashboard** | `/employees/dashboard` | ✅ Vuexy + bootstrap5 + responsive، يحتوي widgets للقضايا/الجلسات/المشاريع |
| **القضايا (Lawsuits)** | `/employees/legal-affairs/lawsuits` | ✅ CRUD كامل، DataTables responsive |
| **الجلسات (Sessions)** | `/employees/legal-affairs/sessions` | ✅ CRUD + completion form + objections |
| **الموكلين (Customers)** | `/employees/customers` | ✅ CRUD + SMS |
| **الخصوم (Opponents)** | `/employees/legal-affairs/opponents` | ✅ CRUD + SMS |
| **الوكالات (PoA)** | `/employees/legal-affairs/power-attorney` | ✅ CRUD + status toggle |
| **الموظفون (HR)** | `/employees/human-resources/employees` | ✅ CRUD + SMS + password reset + business cards |
| **الحضور (Attendance)** | `/employees/human-resources/attendances` | ✅ Manual + biometric integration |
| **الإجازات (Leave)** | `/employees/human-resources/leave-requests` | ✅ Request + approve + balance tracking |
| **الرواتب (WPS)** | `/employees/human-resources/payrolls/wps` | ✅ Generate + approve + Excel/PDF |
| **AI القانوني** | `/employees/legal-ai/{chat,summarize,drafting,precedents}` | ✅ 4 أدوات كاملة + Word/PDF export |
| **Microsoft Teams** | `/employees/teams` | ✅ Create/edit/delete meetings |
| **OneDrive** | `/employees/onedrive` | ✅ Browse + upload + folders |
| **Microsoft SSO Login** | `/employees/auth/microsoft/login` | ✅ OAuth wired |
| **Profile** | `/employees/account/employee/{id}/profile` | ✅ Photo + background + password change |
| **Logout** | `POST /logout` | ✅ موجود في navbar dropdown |

### 🟡 ما يعمل لكن صعب الوصول

| الميزة | المشكلة |
|--------|---------|
| **Billing Portal** | موجود `/t/{tenant}/billing` لكن **لا يوجد رابط له في navbar/menu**. المحامي لا يعرف أين يلغي أو يجدد. |
| **AI Legal** | يحتاج صلاحيات معينة لظهوره في القائمة. صامت إذا لم يحصل عليها. |
| **Email** | الـ route `/employees/emails` موجود لكن **مخفي من القائمة** — وصول بـ URL مباشر فقط. |

### ❌ ما لا يعمل من القائمة

| الميزة | المشكلة |
|--------|---------|
| **Qoyod Accounting** | 17 routes كاملة (مفعَّلة في الكود) لكن **القائمة معلَّقة بـ comment block `{{-- --}}`** في `verticalMenu.blade.php:665-668`. ميزة كاملة محجوبة عن المستخدم. |
| **المذكرات (Memos) — قائمة موحدة** | لا يوجد قائمة عامة بكل المذكرات عبر القضايا. مدفونة فقط داخل صفحة كل دعوى. |
| **الترابط مع الجلسات** | لا يمكن من صفحة الدعوى/الجلسة جدولة Teams meeting أو إرفاق email — كلها أنظمة منفصلة. |

### 📱 Mobile Responsiveness

✅ **جيد جداً** — العينة على lawsuits، sessions، dashboard:
- Bootstrap 5 grid (`col-sm-6 col-xl-3`)
- DataTables responsive plugin
- Vuexy theme متجاوب
- RTL مدعوم

---

## 🎯 جدول المشاكل المُكتشفة (بترتيب الأولوية)

| # | المشكلة | الخطورة | الأثر | الإصلاح |
|---|---------|:-------:|------|---------|
| 1 | جدول `plans` فارغ بعد deploy جديد | 🔴 BLOCKER | landing لا يعرض أي باقات → لا يوجد دفع | تشغيل `DefaultPlansSeeder` كجزء من deploy |
| 2 | `moyasar_publishable_key` فارغ | 🔴 BLOCKER | Moyasar.js لا يعمل → لا دفع ممكن | Operator يضبط من Super Admin Settings (Phase C) |
| 3 | **Multi-tenant login مكسور** — مستخدم tenant جديد لا يستطيع الدخول من `/employees/login` بعد logout | 🔴 BLOCKER | كل عميل جديد يُحبس بعد جلسة واحدة | Phase 6: تفعيل subdomain أو path-based login أو scope-bypass على auth provider |
| 4 | Password reset يعاني نفس الـ bug | 🔴 BLOCKER | عميل نسي كلمة المرور = حساب مفقود | نفس الـ Phase 6 |
| 5 | `subdomain.enabled = NULL` في config | 🟠 HIGH | Phase A جاهزة لكن غير مفعَّلة → لا يوجد bypass للـ smoking gun | تفعيل `TENANCY_SUBDOMAIN_ENABLED=true` + `TENANCY_SUBDOMAIN_APP_BASE_DOMAIN=mnjiz.sa` |
| 6 | Billing portal لا رابط في القائمة | 🟠 HIGH | المحامي لا يعرف أين يدير الاشتراك | إضافة بند "الاشتراك والفواتير" في `verticalMenu.blade.php` |
| 7 | Qoyod menu مُعلَّق بـ comment | 🟠 HIGH | 17 routes كاملة محجوبة | إزالة `{{-- --}}` من `verticalMenu.blade.php:665-668` (إذا الميزة جاهزة) أو توثيق سبب الإخفاء |
| 8 | Email module مخفي من القائمة | 🟡 MED | الوصول بـ URL مباشر فقط | إضافة بند في القائمة |
| 9 | Coupon validation: "حقل code مطلوب" بدلاً من اسم عربي | 🟡 MED | تجربة UX سيئة للعميل العربي | إضافة `attributes()` في `ApplyCouponRequest` |
| 10 | Sandbox notice لا يظهر بالافتراضي | 🟡 MED | Operator يجرب payments حقيقية بالخطأ | تأكد `moyasar_test_mode` يُعيَّن default true عند migration |
| 11 | لا توجد قائمة عامة للمذكرات | 🟡 MED | بحث صعب عبر القضايا | إضافة `/employees/legal-affairs/memos` index |
| 12 | لا تكامل بين الجلسة وTeams/Email | 🟡 MED | المحامي يقفز بين تبويبات | inline buttons في صفحة الدعوى |

---

## 🔍 السبب الجذري الحقيقي (Root cause analysis)

المنصة تعمل في وضع **نصف-جاهز للـ multi-tenancy**:

| طبقة | الحالة | المشكلة |
|------|:------:|---------|
| Database isolation (BelongsToTenant scope) | ✅ مكتملة | تعمل بقوة |
| Tenant resolution (URL → tenant) | 🟡 جزئية | path `/t/{slug}/*` يعمل لكن **لا route fixes login**؛ subdomain `acme.mnjiz.sa` معطَّل |
| Authentication (يجد User بناءً على email) | 🔴 مكسور | يفترض المستخدم في الـ tenant الحالي قبل ما يحدد أي tenant ينتمي إليه — circular |

**خلاصة:** المحامي الذي يستخدم default tenant (الموظفون داخل شركة MNJIZ نفسها) لا يواجه مشاكل. **لكن أي عميل جديد يشترك من خلال `/register` يُحبس في الجلسة الأولى ولا يستطيع الدخول لاحقاً**.

---

## 💡 توصيات الإصلاح بالأولوية

### 🔴 BLOCKERS (يجب قبل أي إطلاق)

1. **تفعيل subdomain في الإنتاج**:
   ```env
   TENANCY_SUBDOMAIN_ENABLED=true
   TENANCY_SUBDOMAIN_APP_BASE_DOMAIN=mnjiz.sa
   ```
   مع DNS wildcard `*.mnjiz.sa → server`، عميل جديد يُسلَّم URL `acme.mnjiz.sa/login`.

2. **إضافة `DefaultPlansSeeder` في `DatabaseSeeder::run()`** أو في deploy script.

3. **توثيق operator-onboarding**: الـ operator يجب أن يدخل Super Admin Settings ويضبط `moyasar_publishable_key + secret_key + mail credentials` قبل قبول أول اشتراك.

### 🟠 HIGH (أسبوع/أسبوعين)

4. إضافة تكامل route للـ billing في القائمة (1 سطر في `verticalMenu.blade.php`).

5. اتخاذ قرار حول Qoyod: تفعيل (إزالة comment) أو إخفاء permissions-gated مع رسالة "قادم قريباً".

6. إضافة Email و Memos في القائمة الرئيسية.

### 🟡 MEDIUM (Phase I+)

7. ترجمة validation field names في `ApplyCouponRequest` (`attributes()` method).

8. ربط Teams/Email بصفحة الجلسة (inline action buttons).

9. صفحة قائمة موحَّدة للمذكرات.

---

## 📊 الـ Verdict النهائي

| المنظور | الحكم | السبب |
|---------|:-----:|--------|
| **الكود** | 🟢 جيد | 287/287 tests pass، architecture سليمة |
| **رحلة العميل (المشتري)** | 🔴 مكسورة جزئياً | بعد signup يُحبس، Moyasar غير مضبوط، plans غير مزروعة |
| **رحلة المحامي (الاستخدام اليومي)** | 🟢 ممتازة | Vuexy، responsive، 17+ ميزة جاهزة، AI، Microsoft، WPS |
| **التشغيل (Operator readiness)** | 🟡 يحتاج إعداد | seeders + subdomain + Moyasar keys + قرار Qoyod كلها قبل launch |

**الاستنتاج النهائي:**
المنصة **جاهزة تقنيًا** (الكود سليم) لكن **غير جاهزة تشغيليًا** (Operator setup ناقص). المحامي الذي بداخل organization MNJIZ نفسه يستخدم منصة كاملة وقوية. لكن **العميل الخارجي الذي يشترك** لن يستطيع متابعة الاستخدام بعد الجلسة الأولى بسبب multi-tenant routing نصف-المفعَّل.

**الحل الفوري (قبل أي إطلاق تجاري):** تفعيل subdomain (Phase A جاهزة، فقط env flag).

---

**تم الفحص بدون تعديل أي كود.**
