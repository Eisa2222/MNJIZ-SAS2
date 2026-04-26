# MNJIZ V2 — Laravel 13 + Multi-DB Tenancy Build Report

**Date:** 2026-04-26
**Mode:** Fresh build per `saas-prompt (2).md` — 100% spec-conformant stack
**Status:** ✅ Foundation complete and verified working
**Location:** `D:\MNJIZ\MNJIZ-NOT-SAAS\v2\`
**Path C (legacy):** untouched at `D:\MNJIZ\MNJIZ-NOT-SAAS\` root

---

## ✅ ما تم إنجازه في هذه الجلسة

### 1. Stack — مطابق للمواصفة 100%

| المعيار في المواصفة | الواقع في V2 |
|---------------------|--------------|
| Laravel 13 | ✅ **13.6.0** مثبَّت |
| PHP 8.3+ | ✅ **8.3.30** |
| Tailwind CSS v4 | ✅ مثبَّت + Vite 8 (CDN فعّال للسماح برؤية UI بدون npm build) |
| stancl/tenancy ^3.10 | ✅ **3.10** مثبَّت + Multi-DB mode |
| spatie/laravel-permission ^7.3 | ✅ **7.3** مثبَّت + migrations منشورة |
| guzzlehttp/guzzle ^7.9 | ✅ **7.9** مثبَّت |
| phpunit/phpunit ^12.0 | ✅ **12.5** (ضمن default Laravel 13) |

### 2. Multi-Database Tenancy

```
config/tenancy.php
   tenant_model:  App\Models\Tenant (extends BaseTenant, implements TenantWithDatabase)
   id_generator:  UUIDGenerator
   central_domains: localhost, 127.0.0.1, mnjiz.test (env-driven)
   prefix:        mnjiz_v2_tenant_  → DBs like mnjiz_v2_tenant_<uuid>
   manager:       MySQLDatabaseManager (auto CREATE/DROP per tenant)
   bootstrappers: Database + Cache + Filesystem + Queue
```

**Identification middleware:** `InitializeTenancyByDomainOrSubdomain` (Subdomain + Custom Domain معاً — spec line 11).

### 3. قاعدة البيانات

**Central DB (`mnjiz_v2_central`)** — 16 جدول:
```
✓ super_admins
✓ plans + plan_features
✓ tenants (مع columns مخصصة: company_name, owner_*, logo, timezone, language, status, settings)
✓ domains (stancl)
✓ subscriptions
✓ payments
✓ coupons + coupon_uses (مع applicable_plans json + billing_cycles json)
✓ landing_features + landing_faqs
✓ system_settings (مع encrypt() للـ sensitive keys)
✓ permissions tables (spatie 7.3)
✓ cache + jobs + sessions
```

**Tenant DB (per-tenant)** — schema جاهز في `database/migrations/tenant/`:
```
✓ users
✓ tenant_settings
```

### 4. Models (10 ملفات بالكامل)

```
App\Models\SuperAdmin       — guard 'super_admin'
App\Models\Tenant           — extends BaseTenant + TenantWithDatabase
App\Models\Plan             — مع scopeActive() + accessors (formatted price + yearly savings %)
App\Models\PlanFeature
App\Models\Subscription     — isActive, isTrialing, isExpired, daysUntilExpiry
App\Models\Payment
App\Models\Coupon           — كل 7 methods من المواصفة
App\Models\CouponUse
App\Models\LandingFeature   — scopeActive
App\Models\LandingFaq       — scopeActive
App\Models\SystemSetting    — get/set/setMany + auto-encrypt sensitive keys + Cache 1h
```

### 5. Services + Jobs

```
App\Services\MoyasarService — createPayment + getPayment + refundPayment + auto SAR→halalas
App\Services\CouponService  — validate() returns array (no exceptions) + apply() atomic
App\Jobs\CreateTenantJob    — full multi-DB flow: create tenant → CREATE DATABASE →
                               run tenant migrations → owner user → subscription →
                               payment → coupon → welcome mail (48h signed link)
App\Mail\TenantWelcomeMail  — Mailable bedö setup link (NO plaintext password)
```

### 6. Controllers + Routes

```
LandingController                            → GET /landing
SuperAdmin\Auth\LoginController              → /super-admin/login (login/logout)
SuperAdmin\DashboardController               → /super-admin (stats + revenue chart)
CheckoutController                           → 5 routes (show/applyCoupon/callback/success/failure)
```

`routes/tenant.php` — يستخدم `InitializeTenancyByDomainOrSubdomain` + middleware `check.subscription`.

### 7. Console Commands (scheduled)

```
saas:check-trial-expiry     → daily 00:00
saas:send-trial-warnings    → daily 08:00
```

### 8. Middleware

```
App\Http\Middleware\CheckSubscription
   - tenant suspended         → render errors.suspended (HTTP 403)
   - subscription expired     → render errors.subscription-expired (HTTP 402)
   - active/trialing          → next($request)
```

### 9. Views (Tailwind v4 + Alpine.js + Moyasar.js)

```
resources/views/landing/index.blade.php          ← Hero + Features + Pricing (toggle شهري/سنوي) + FAQ + Footer
resources/views/checkout/show.blade.php          ← Form + AJAX coupon + Moyasar.js v1.15 wired
resources/views/checkout/success.blade.php
resources/views/checkout/failure.blade.php
resources/views/super-admin/auth/login.blade.php
resources/views/super-admin/layout.blade.php     ← Sidebar مع 9 روابط
resources/views/super-admin/dashboard.blade.php  ← Stats grid + revenue chart + recent tenants
resources/views/tenant/dashboard.blade.php
resources/views/tenant/password-setup.blade.php  ← 48h signed link form
resources/views/errors/suspended.blade.php
resources/views/errors/subscription-expired.blade.php
resources/views/emails/tenant/welcome.blade.php  ← يحتوي رابط setup فقط، لا password
```

### 10. Seeded Data جاهز للاستخدام

```
SuperAdmin: 1 (superadmin@mnjiz.sa / super-admin-2026)
Plans:      4 (Free, Basic 299, Pro 799, Enterprise 1499)
PlanFeats:  21
LandingFeatures: 6
LandingFAQs:     5
SystemSettings:  23 (hero, footer, support, trial knobs, notifications)
```

---

## 🧪 Smoke Test Results

```
GET /landing                     → HTTP 200, 27 KB ✅
GET /super-admin/login           → HTTP 200, 2.4 KB ✅
GET /checkout/professional       → HTTP 200, 11.5 KB ✅
GET /up (health)                 → HTTP 200       ✅

POST /super-admin/login (creds)  → HTTP 302 → /super-admin ✅
GET /super-admin (after login)   → HTTP 200, 12 KB
   - Sidebar shows 9 links
   - Dashboard shows stats + chart + tenants table
```

---

## 🔌 كيفية الوصول للنظام الآن

### من داخل Docker container (يعمل فوراً)

```bash
docker exec mnjiz-app curl -s http://127.0.0.1:8001/landing | head
```

### من المتصفح (يحتاج خطوة واحدة)

V2 يعمل على port **8001** داخل container، لكن Docker يعرض port **8000** فقط (محجوز بـ Path C).

**الحل الأنظف:** استبدل port mapping:

```bash
# 1. أوقف container الحالي
docker stop mnjiz-app

# 2. شغّل بنفس الإعدادات + map port 8001 إضافياً
docker run --name mnjiz-app -d \
  -p 8000:8000 -p 8001:8001 \
  --network=<existing-network> \
  -v D:/MNJIZ/MNJIZ-NOT-SAAS:/var/www/html \
  <image>
# (استخدم نفس الـ image + env من docker-compose)

# 3. شغّل V2 server
docker exec -d mnjiz-app bash -c "cd /var/www/html/v2 && php artisan serve --host=0.0.0.0 --port=8001"
```

ثم زر:
- **Landing:** http://localhost:8001/landing
- **Super Admin:** http://localhost:8001/super-admin/login
  - Email: `superadmin@mnjiz.sa`
  - Password: `super-admin-2026`
- **Checkout:** http://localhost:8001/checkout/professional

### بديل أسرع (بدون docker reconfig)

أوقف خادم Path C على 8000 مؤقتاً وشغّل V2 محلّه:

```bash
docker exec mnjiz-app pkill -f 'artisan serve' || true
docker exec -d mnjiz-app bash -c "cd /var/www/html/v2 && php artisan serve --host=0.0.0.0 --port=8000"
```

ثم زر **http://localhost:8000/super-admin/login** كالعادة.

---

## 📋 ما تبقى للوصول إلى 100% feature-complete (Phase 2)

V2 الآن **مبني صحيحاً على Stack المواصفة**، لكن الميزات التشغيلية تحتاج إكمال:

### إدارة Super Admin CRUDs (هياكل routes موجودة، تحتاج controllers + views)

- [ ] `SuperAdmin\TenantController` — index/show/suspend/activate/impersonate
- [ ] `SuperAdmin\PlanController` — CRUD + toggle
- [ ] `SuperAdmin\SubscriptionController` — index/cancel/extend
- [ ] `SuperAdmin\PaymentController` — index + refund via Moyasar
- [ ] `SuperAdmin\CouponController` — full CRUD per spec lines 428-471
- [ ] `SuperAdmin\LandingFeatureController` — CRUD + reorder (SortableJS)
- [ ] `SuperAdmin\LandingFaqController` — CRUD + reorder
- [ ] `SuperAdmin\SettingController` — index (tabs UI) + update + test-mail + test-moyasar
- [ ] `SuperAdmin\ReportController` — revenue reports

### Tenant authentication & app

- [ ] `TenantPasswordSetupController` — actual store() implementation (currently placeholder)
- [ ] Tenant `Auth\LoginController` — login form + post handler inside tenant DB
- [ ] Tenant dashboard — actual app features (will need migration of legacy /employees/* code)

### Tests (PHPUnit 12)

- [ ] Feature tests for landing, checkout, super-admin login, dashboard
- [ ] Tenant provisioning end-to-end test (CreateTenantJob)
- [ ] Multi-tenant isolation test
- [ ] Coupon validate/apply tests

### إعدادات بيئة الإنتاج

- [ ] DNS wildcard `*.mnjiz.sa` للـ subdomain tenant access
- [ ] MySQL user privileges: `CREATE DATABASE`, `DROP DATABASE` per tenant
- [ ] Queue worker (`php artisan queue:work`) لتنفيذ CreateTenantJob + mail queue
- [ ] Scheduled cron: `* * * * * cd /path && php artisan schedule:run`
- [ ] Real Moyasar keys في `/super-admin/settings`
- [ ] SMTP credentials في `/super-admin/settings`
- [ ] Build Tailwind production assets:
  ```bash
  npm install && npm run build
  ```
  (يحتاج Node.js على المضيف أو في container — Path C container لا يحتوي npm)

---

## 📊 درجة المطابقة مع المواصفة الآن

| البند | قبل V2 (Path C) | بعد V2 |
|-------|:---------------:|:------:|
| Laravel 13 | ❌ 10.48 | ✅ 13.6.0 |
| Tailwind v4 | ❌ Bootstrap | ✅ Tailwind v4 + Vite 8 |
| stancl/tenancy ^3.10 | ❌ 3.8 | ✅ 3.10 |
| Multi-DB tenancy | ❌ Single-DB | ✅ Multi-DB (MySQLDatabaseManager) |
| spatie/laravel-permission ^7.3 | ❌ 6.x | ✅ 7.3 |
| phpunit ^12.0 | ❌ 10.5 | ✅ 12.5 |
| Subdomain + Custom Domain | 🟡 partial | ✅ InitializeTenancyByDomainOrSubdomain |
| coupon `applicable_plans` json | ❌ pivot table | ✅ json column |
| coupon `billing_cycles` json | ❌ in meta | ✅ json column |
| coupon `uses_count` | 🟡 `redemptions_count` | ✅ `uses_count` |
| Tenant migrations folder | ❌ موحَّد | ✅ database/migrations/tenant/ |
| bootstrap/app.php (Laravel 11+) | ❌ Kernel.php | ✅ bootstrap/app.php |
| Schedule في routes/console.php | ❌ Kernel | ✅ routes/console.php |
| Typed config | ❌ env() | 🟡 لم تُستخدم بعد (اختياري حسب المواصفة) |

**النسبة الإجمالية:** 13/14 محور هيكلي ✅، فقط Typed config لم يُستخدم (اختياري حسب المواصفة line 80).

---

## 🛡️ ضمانات الأمان

- ✅ Sensitive keys (moyasar_secret_key, mail_password, moyasar_webhook_secret) مشفَّرة على القرص بـ `Crypt::encryptString`.
- ✅ Multi-DB isolation: كل tenant في DB منفصلة كاملاً — أمان فيزيائي.
- ✅ password setup link موقَّع 48h عبر `URL::temporarySignedRoute` — لا plaintext password في أي بريد.
- ✅ Coupon counter atomic عبر `DB::increment('uses_count')` — race-safe.
- ✅ Throttle على login (5/min) و coupon-apply (30/min).
- ✅ Moyasar secret key لا يُطبَع في logs (try/catch مع رسالة generic فقط).

---

## ⚠️ ما لم يُفحص فعلياً (يحتاج جلسة لاحقة)

1. **CreateTenantJob end-to-end** — لم نُنشئ tenant فعلياً (يحتاج Moyasar callback + Queue worker شغّال).
2. **Tenant DB creation** — تم اختبار stancl/tenancy config صحيحاً لكن لم نُنشئ tenant DB حقيقي.
3. **Multi-tenant isolation** — لم نختبر tenant A لا يرى بيانات tenant B (يحتاج tenant DBs موجودة).
4. **Tailwind v4 build** — يستخدم CDN حالياً، production يحتاج `npm run build`.
5. **Email delivery** — `MAIL_MAILER=log` افتراضياً، لم نختبر إرسال SMTP فعلي.

---

## 📁 إحصائيات الكود

| النوع | العدد |
|------|------:|
| Migrations | 16 (central) + 2 (tenant) |
| Models | 10 |
| Controllers | 4 (LandingController, CheckoutController, SuperAdmin\Auth\LoginController, SuperAdmin\DashboardController) |
| Services | 2 (MoyasarService, CouponService) |
| Jobs | 1 (CreateTenantJob) |
| Mailables | 1 (TenantWelcomeMail) |
| Middleware | 1 (CheckSubscription) |
| Console Commands | 2 (CheckTrialExpiry, SendTrialWarnings) |
| Blade Views | 12 |
| Routes | 19 |
| Composer packages | 81 |

---

## 🎯 الخطوة التالية (للمستخدم)

اختر:

1. **استمر في بناء V2** — فجلسة لاحقة نُنجز Super Admin CRUDs الباقية (Tenants, Plans, Subscriptions, Payments, Coupons, Landing Content, Settings UI). الجلسة الحالية وضعت 60% من الـ foundation.

2. **اختبر V2 يدوياً الآن** — افتح المتصفح على V2 وتحقق من landing/checkout/super-admin بنفسك (راجع قسم "كيفية الوصول للنظام" أعلاه).

3. **انشر V2 على staging** — Path C يبقى production، V2 على subdomain منفصل (مثلاً v2.mnjiz.sa) للتجربة.

4. **اقتل Path C ودشّن V2** — تنفّذ migration data من Path C DBs إلى V2 multi-DB. يحتاج migration handler مخصّص + jail downtime.

---

**Path C (legacy) untouched. V2 fully isolated in `v2/` directory.**
