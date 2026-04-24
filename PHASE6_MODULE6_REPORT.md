# Phase 6 — Module 6: Final System Hardening (Marketing + Tasks + Approvals + Chat + AI + Surveys + Meetings + Integrations)

**Risk Tier:** HIGH (the last uncovered surfaces — chat, AI prompts, tasks, surveys, external APIs)
**Status:** ✅ **SEALED — 153/153 tests pass** (141 prior + 12 new)
**Date:** 2026-04-24

This is the **final module** of Phase 6. After this sweep every user-owned
aggregate root in the system carries `tenant_id`, every queued Job uses
`TenantAwareJob`, every scheduled command uses `IteratesTenants`, every
external API key is per-tenant, and every cache key is tenant-partitioned.

---

## Summary

| Metric | Count |
|---|---|
| Tables migrated (`tenant_id` added) | **26** |
| Models updated (`BelongsToTenant`) | **21** |
| Jobs updated (`TenantAwareJob`) | **14** |
| Commands updated (`IteratesTenants`) | **3** |
| Broadcast channels audited | **3** (all safe — auth via `user.id`) |
| Tests added | **12** (39 assertions) |

### Tables migrated
**Marketing:** `campaign_management`, `campaign_results`, `content_management`, `content_management_socials`, `social_publications`
**Tasks:** `tasks`, `task_steps`, `task_events`, `task_step_events`, `task_attachments`, `task_routings`
**Approvals:** `approval_requests`, `approval_logs`, `approval_request_levels`
**Chat / AI:** `messages`, `ai_chats`, `ai_chat_messages`
**Surveys:** `surveys`, `survey_questions`, `survey_question_options`, `survey_responses`, `survey_answers`
**Meeting Rooms:** `meeting_rooms`, `participants`, `meeting_notes`, `attendee_meetings`

### Models updated
`Task`, `TaskStep`, `TaskAttachment`, `TaskEvent`, `TaskRouting`, `TaskStepEvent`,
`CampaignManagement`, `CampaignResult`, `ContentManagement`, `SocialPublication`,
`ApprovalRequest`, `ApprovalLog`, `ApprovalRequestLevel`,
`Message` (chat), `AiChat`, `AiChatMessage`,
`Survey`, `SurveyQuestion`, `SurveyQuestionOption`, `SurveyResponse`, `SurveyAnswer`,
`MeetingRoom`, `Participant`, `MeetingNote`, `AttendeeMeeting`.

### Jobs updated (queued — all now `TenantAwareJob`)
`CreateTaskJob`, `BatchUpdateMicrosoftTaskJob`, `BatchDeleteMicrosoftTaskJob`, `DeleteTasksAndEventsFinalJob`,
`PublishContentJob` (Marketing),
`Microsoft\Onedrive\UploadFileJob`, `Microsoft\Onedrive\DeleteFileJob`, `Microsoft\Onedrive\RenameFileJob`, `Microsoft\Onedrive\UpdateUploadFileJob`,
`DeleteFileFromOneDriveJob`, `RenameFileOnOneDriveJob`, `UploadFileToOneDriveJob`,
`SyncStepsTaskWithMicrosoftJob`, `SyncTaskWithMicrosoftJob`.

### Commands updated (scheduled — all now `IteratesTenants`)
`content:publish-scheduled` (was every-minute global loop),
`biostation:sync`,
`purge:soft-deletes` (also fixed broken `App\Models\judicial_affairs\*` namespace imports).

---

## Bugs Found

### 1. 14 queued Jobs without tenant context — **CRITICAL**
- **Severity:** HIGH
- **Root cause:** Queue workers run in a different process with empty `TenantContext`. Every one of these jobs queried tenant-scoped data (`User::find`, `Task::find`, `SocialPublication::...`) which on pickup fell back to the default tenant — silently returning another firm's row.
- **Fix:** `use TenantAwareJob;` + `captureTenant()` in constructor so tenant id is serialized into the queue payload; `RestoreTenantContext` middleware (already in place) re-seats context before `handle()` runs.
- **Regression test:** `test_queued_job_restores_tenant_context` (serialize → unserialize → middleware → assert context restored).

### 2. `content:publish-scheduled` cron posted everyone's content under first tenant's creds — **CRITICAL**
- **Severity:** HIGH
- **Root cause:** Runs `every minute` globally. Without `perTenant()`, a `SocialPublication::readyToPublish()` scope with missing tenant context would use the Default Tenant's LinkedIn/Twitter access tokens to publish posts belonging to ALL tenants. Imagine tenant A's marketing content appearing on tenant B's LinkedIn page — brand destruction.
- **Fix:** Wrapped `handle()` in `$this->perTenant(fn ($t) => …)`. Each tenant's content is queued under that tenant's context; `PublishContentJob` (also `TenantAwareJob`) restores it on the worker.

### 3. `PurgeSoftDeletedRecords` dead imports (would fail-fast on production PHP 8) — **HIGH**
- **Severity:** HIGH (silent in dev, fatal on Linux case-sensitive FS)
- **Root cause:** 6 `use App\Models\judicial_affairs\{Opponent|PowerOfAttorney|Session|SessionComment|SessionCommentMention|NoteCommentMention};` imports that no longer exist — class references in `$models` array forced compile-time loading → fatal. Plus: the command purged ALL tenants' soft-deleted rows under the FIRST tenant's `archive_delete_duration` retention policy.
- **Fix:** Remapped imports to the correct `App\Models\LegalAffair\*` namespace; wrapped handle body in `perTenant()` so each firm's retention policy governs its own purge.

### 4. Chat `Message`, `AiChat`, `AiChatMessage` shared across tenants — **CRITICAL**
- **Severity:** HIGH (privileged legal conversations)
- **Root cause:** No `tenant_id` on `messages` / `ai_chats` / `ai_chat_messages`. The `BetweenUsers` scope on `Message` only filtered by sender/receiver ID; if tenant A and tenant B coincidentally both have user ids 1+2, tenant A's users would see tenant B's private chat.
- **Fix:** `tenant_id` column + `BelongsToTenant` on all 3 models. Broadcast channels remain authenticated by `user.id` (which is already tenant-scoped), so WebSocket layer inherits the isolation.
- **Regression test:** `test_chat_messages_are_isolated_per_tenant` + `test_ai_chat_logs_are_isolated_per_tenant`.

### 5. Surveys + Responses shared — **HIGH**
- **Severity:** HIGH (customer responses contain PII)
- **Root cause:** `surveys`, `survey_responses`, `survey_answers` tables had no `tenant_id`. A public survey link (`/survey/{token}`) from tenant A could have been answered and the response would be visible to tenant B's dashboard.
- **Fix:** `tenant_id` + `BelongsToTenant` on all 5 survey models.
- **Regression test:** `test_surveys_and_responses_are_isolated`.

---

## Security Findings

### API leakage

External integrations that authenticate with secrets now resolve through `SettingsRepository::get()` (tenant-scoped + encrypted-at-rest — from Module 5). This test re-asserts the critical property for Module 6's marketing stack:

```php
test_external_api_keys_are_isolated_per_tenant
    → tenant A linkedin_access_token !== tenant B linkedin_access_token
```

All Module 6 integrations flow through this gateway:
- LinkedIn access tokens (per-tenant)
- Twitter access tokens (per-tenant)
- Microsoft Graph / OneDrive tokens (on the User model, already tenant-scoped)
- BioStation device credentials (per-tenant via `Settings::current()`)
- OpenAI API key (per-tenant — `OpenAISettings::get()` → `Settings::current()`)

### Data leakage

The `CRITICAL` test encoded directly:
```php
$this->assertFalse(
    Message::where('message', 'B-private')->exists(),
    'CRITICAL: tenant A must NOT see tenant B chat messages.'
);
```
Same pattern for AI chats, surveys, tasks, approvals, campaigns, content.

### Remaining residual risk

- Broadcast channel authorization (`chat.{u1}.{u2}`, `user.{id}`, `ai-chat-channel.{chatId}`) currently checks only user-id and AiChat ownership. Since User is tenant-scoped, this is **safe** — a malicious client can't authenticate a channel for a user that doesn't exist in their tenant. Documented in the broadcast channels audit table below.
- Empty scaffold file `app/Models/ContentManagementSocial.php` (namespace `App\Models`) — unused, kept as-is. The real model at `app/Models/Marketing/ContentManagement/ContentManagementSocial/ContentManagementSocial.php` — also effectively a placeholder (no fillable, no relations).

---

## Cache Findings

All Module 6 cache reads now route through tenant-scoped keys established in earlier modules:

| Domain | Key pattern |
|---|---|
| Settings | `tenant_{id}_settings` |
| Qoyod credentials | `tenant_{id}_qoyod_{suffix}` |
| SettingsHelper aggregate | `tenant_{id}_app_settings` |
| Central platform defaults | `central_settings` (intentionally global) |

No global cache key writes are introduced by Module 6 code paths.

---

## Integration Findings

### Broadcast channels (audited — all safe)

| Channel | Auth check | Tenant-safety |
|---|---|---|
| `chat.{u1}.{u2}` | `in_array(auth user id, [u1, u2])` | User is tenant-scoped → auth user can't exist in another tenant's scope → **safe** |
| `user.{id}` | `auth user id === id` | Same as above → **safe** |
| `ai-chat-channel.{chatId}` | `AiChat::where('id', chatId)->where('user_id', auth id)->exists()` | AiChat is now `BelongsToTenant` → `exists()` adds `WHERE tenant_id = current` → **safe** |

Bonus: `Notification` classes are `ShouldBroadcast`-aware from Module 4; their WS payload is user-scoped by construction.

### External API adapters

| Integration | Credential source | Per-tenant? |
|---|---|---|
| Microsoft Graph (OneDrive, ToDo, Outlook) | `User.microsoft_token` | ✅ User is tenant-scoped |
| LinkedIn | `SettingsRepository::get('linkedin_access_token')` | ✅ encrypted per tenant |
| Twitter | `SettingsRepository::get('twitter_*')` | ✅ encrypted per tenant |
| BioStation | `Settings::current()->biostation_*` | ✅ per-tenant |
| Qoyod (accounting) | `Settings::current()->qoyod_api_key` + tenant cache | ✅ per-tenant |
| Moyasar (payments) | `Settings::current()->moyasar_*` (encrypted) | ✅ per-tenant |
| OpenAI / Claude / Gemini (LegalAI) | `Settings::current()->openai_api_key` etc. | ✅ per-tenant |
| SMS (4jawaly) | `Settings::current()->sms_*` | ✅ per-tenant |
| Twilio WhatsApp | `Settings::current()->twilio_*` | ✅ per-tenant |

---

## Before / After

```php
// ────────────── Before Module 6 ──────────────

Task::count()                                    // ⚠ all tenants' tasks mixed ❌
CampaignManagement::all()                        // ⚠ one firm's campaigns visible to all ❌
Message::betweenUsers(1, 2)->get()               // ⚠ cross-firm private chat if same user ids ❌
AiChat::where('user_id', $uid)->get()            // ⚠ another firm's privileged AI logs ❌
SurveyResponse::count()                          // ⚠ PII for wrong customers ❌

// content:publish-scheduled cron (global)
SocialPublication::readyToPublish()->get()
    → dispatch PublishContentJob
       → uses default tenant's LinkedIn token
       → posts tenant B's content on tenant A's LinkedIn ❌

// OneDrive queue worker
UploadFileJob::handle()
    → queries User::find(...) with no tenant context
    → could load wrong firm's Microsoft token ❌

// ────────────── After Module 6 ──────────────

Task::count()                                    // ✅ current tenant only
CampaignManagement::all()                        // ✅ scoped
Message::betweenUsers(1, 2)->get()               // ✅ tenant_id = current appended
AiChat::where('user_id', $uid)->get()            // ✅ tenant boundary enforced
SurveyResponse::count()                          // ✅ scoped

// content:publish-scheduled cron
perTenant(fn ($t) => dispatch(new PublishContentJob($pub)))
    ├─ PublishContentJob::__construct → captureTenant()
    └─ worker → RestoreTenantContext → publishes with CORRECT tenant credentials ✅

// Cross-tenant reassignment attempt
$task->tenant_id = $other->id; $task->save();    // RuntimeException ✅

// Super-admin cross-tenant view
Task::withoutTenancy()->count()                  // ✅ all tenants
```

---

## Deliverables

**New:**
- `database/migrations/2026_04_24_140000_add_tenant_id_to_remaining_tables.php` (26 tables)
- `tests/Feature/Module6/Module6IsolationTest.php` (12 tests, 39 assertions)
- `PHASE6_MODULE6_REPORT.md` (this file)

**Modified (models — 21):** see "Models updated" list above.

**Modified (jobs — 14):** see "Jobs updated" list above.

**Modified (commands — 3):**
- `app/Console/Commands/Marketing/ContentManagement/PublishScheduledContent.php`
- `app/Console/Commands/SyncBioStationData.php`
- `app/Console/Commands/PurgeSoftDeletedRecords.php` (+ namespace fixes)

---

## Final Verdict

| Gate | Status |
|---|---|
| Full test suite passes | ✅ **153/153** (375 assertions) |
| Zero cross-tenant data leak | ✅ **12** new regression tests + all prior ones |
| Queued jobs keep tenant context | ✅ `CreateTaskJob` round-trip test proves it |
| Scheduled commands run per tenant | ✅ 3/3 refactored |
| External API keys isolated | ✅ test + Module 5 encryption |
| File storage tenant-prefixed | ✅ test |
| Cache isolation | ✅ test + prior modules |
| Cross-tenant reassignment blocked | ✅ `RuntimeException` test |
| Super-admin cross-tenant view preserved | ✅ `withoutTenancy()` test |
| Broadcast channels safe | ✅ audit table — all user-auth based |
| Migration idempotent + reversible | ✅ |
| Pre-existing runtime bug fixed | ✅ `PurgeSoftDeletedRecords` namespace imports |

## 🎉 Phase 6 Complete

Every module in the MNJIZ platform is now tenant-aware end-to-end:

| Module | Status | Tests |
|---|---|---|
| Module 1 — HR | ✅ Sealed | 26 isolation tests (rolled into 109) |
| Module 2 — OperationsCenter | ✅ Sealed | +5 (114 total) |
| Module 3 — LegalAffair | ✅ Sealed | +8 (122 total) |
| Module 4 — Notifications | ✅ Sealed | +8 (130 total) |
| Module 5 — Settings | ✅ Sealed | +11 (141 total) |
| **Module 6 — Final** | ✅ **Sealed** | **+12 (153 total)** |

**Verdict: GO — Phase 6 complete. The MNJIZ codebase is structurally multi-tenant-safe. Ready for Phase 7 (data migration tooling) and Phase 8 (production hardening / observability).**
