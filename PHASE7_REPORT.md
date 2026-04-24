# Phase 7 — Data Migration & Cutover Strategy

**Risk Tier:** CRITICAL (production data lifecycle, no-loss guarantee)
**Status:** ✅ SEALED — 163/163 tests pass (153 prior + 10 new)
**Date:** 2026-04-24

---

## Summary

| Metric | Count |
|---|---|
| Tenant-owned tables | **90** (all carry `tenant_id`) |
| Central tables (never scoped) | **17** |
| Lookup / reference tables | **33** |
| Other / unclassified | 42 (mostly pivots + infra tables) |
| Artisan commands created | **5** |
| Audit table created | **1** (`saas_migration_runs`) |
| Settings keys migrated (mappable) | **49** (across 7 groups) |
| File columns registered | **25** (across 11 tables) |
| Tests added | **10** (39 assertions) |

---

## Commands Created

| Command | Purpose | Dry-run |
|---|---|---|
| `saas:migration:inventory` | Read-only classification of every DB table into tenant-owned / central / lookup / other — with row counts, null-tenant_id counts, file-column flags, risk heuristic | n/a (read-only) |
| `saas:migration:backfill-default-tenant` | For every tenant-owned table, sets `tenant_id = default_tenant_id` on rows where it is NULL. Idempotent | ✅ |
| `saas:migration:migrate-settings` | Copies every mappable column of the legacy `settings` singleton into `tenant_settings` key/value rows, encrypted at rest for sensitive keys | ✅ |
| `saas:migration:migrate-files` | Moves files from legacy paths (`attachments/...`, `uploads/sessions/...`) to `tenants/{id}/{subdir}/...` and updates the DB column. Safe copy-then-delete ordering | ✅ |
| `saas:migration:validate` | Post-cutover invariant checker: null `tenant_id`, orphan `tenant_id` values, parent/child tenant boundary consistency, leaky file paths. Exit 0 = GO, non-zero = NO-GO | n/a |

Every command extends a shared `BaseSaasMigrationCommand` that opens a `saas_migration_runs` record at start, accumulates per-step metrics + errors, and closes the record at exit.

### Usage

```bash
# 1) Plan phase — nothing is written.
php artisan saas:migration:inventory
php artisan saas:migration:backfill-default-tenant --dry-run
php artisan saas:migration:migrate-settings        --dry-run
php artisan saas:migration:migrate-files           --dry-run

# 2) Commit phase — run in order (each command is idempotent).
php artisan saas:migration:backfill-default-tenant
php artisan saas:migration:migrate-settings
php artisan saas:migration:migrate-files

# 3) Gate — MUST return exit 0 before cutover.
php artisan saas:migration:validate
```

---

## Validation Results (on dev DB at seal time)

```
— CHECK 1: tenant_id IS NULL —              OK (no rows)
— CHECK 2: orphan tenant_ids —              OK (no orphans)
— CHECK 3: parent/child tenant_id mismatch — OK (all edges consistent)
— CHECK 4: cross-tenant file paths —        OK (no mismatched file paths)
✅ VALIDATE: all invariants hold.
```

Parent/child edges inspected (16 explicit edges, extensible in ValidateCommand):
- `contract_payments → contracts`
- `contract_attachments → contracts`
- `exceptional_contract_approvals → exceptional_contracts`
- `lawsuit_attachments → lawsuits`
- `lawsuit_notes → lawsuits`
- `lawsuit_note_replies → lawsuit_notes`
- `sessions → lawsuits`
- `session_comments → sessions`
- `task_steps → tasks`
- `task_events → tasks`
- `task_step_events → task_steps`
- `survey_questions → surveys`
- `survey_responses → surveys`
- `survey_answers → survey_responses`
- `ai_chat_messages → ai_chats`
- `approval_logs / approval_request_levels → approval_requests`

---

## Bugs / Pitfalls Surfaced

### 1. `BaseSaasMigrationCommand::isDryRun()` crashed on commands without the option
- **Fix:** guard with `$this->getDefinition()->hasOption('dry-run')` before reading the value. Inventory + validate never support dry-run but both extend the base class.

### 2. `SaasMigrationRun.command` captured multi-line signature text
- **Fix:** use `$this->getName()` instead of parsing `$this->signature`. The former returns just `saas:migration:backfill-default-tenant`; the latter includes the argument block.

---

## Security Findings

### Encryption-at-rest (migrate-settings)

19 legacy columns flagged `is_encrypted=true` in the settings map get
written to `tenant_settings.value` via `SettingsRepository::set()`,
which transparently `Crypt::encryptString()`s them:

`microsoft_client_secret`, `microsoft_tenant_id`, `sms_api_key`,
`sms_api_secret`, `twilio_account_sid`, `twilio_auth_token`,
`biostation_api_key`, `openai_api_key`, `qoyod_api_key`, etc.

Assertion test (`test_migrate_settings_writes_encrypted_tenant_settings`)
reads the raw DB column and confirms it is NOT equal to the plaintext
value, then decrypts back to verify the accessor path.

### File move safety

`migrate-files` uses a strict **copy → update DB → delete** ordering:
1. `Storage::copy(source, dest)` — if fails, bail, row unchanged.
2. `DB::update(column = new_relative_path)` — if fails, leaves new file
   in place (harmless duplicate) and logs error.
3. `Storage::delete(source)` — only after both above succeed.

Additional guards:
- Files already under `tenants/{id}/...` are skipped (idempotent re-runs).
- Missing sources are logged and skipped — **never** deleted.
- Destination conflicts are logged and skipped — **never** overwritten.
- Every row of the `--dry-run` report lists `from` → `to` for operations review.

---

## Rollback Plan

Each command is designed with rollback in mind:

| Command | What it writes | How to roll back |
|---|---|---|
| `backfill-default-tenant` | updates `tenant_id` from NULL → default_id | No destructive change: the original data (NULL) was already invalid in a multi-tenant world. A manual SQL rollback `UPDATE t SET tenant_id = NULL WHERE tenant_id = default_id` is possible but rarely needed. |
| `migrate-settings` | inserts/updates `tenant_settings` rows | Does not delete legacy `settings`. Rollback = `DELETE FROM tenant_settings WHERE created_at > $run_started_at AND tenant_id = $default_id`. The `saas_migration_runs.started_at` timestamp gives you the window. |
| `migrate-files` | copies file to new path, updates DB column, deletes source | Source file was copied **before** the DB update. If operations detect an issue after the fact, the new files can be moved back using the `saas_migration_runs.summary` (plus a hand-rolled reverse command using the recorded `from`/`to` pairs). |
| `validate` | read-only | n/a |

**Backup rule (operational):** before any run of `migrate-files` in production, take a filesystem-level snapshot of `storage/app/public/` and a `mysqldump` of the database. Both are required and non-negotiable.

---

## Migration Logs

Every run writes one row to `saas_migration_runs`:

```
id | command                                  | mode    | status  | started_at | finished_at | summary (json)   | errors (json) | created_by
 1 | saas:migration:inventory                 | real    | success | ...        | ...         | {tenant_owned…}  | null          | null
 2 | saas:migration:backfill-default-tenant   | dry-run | success | ...        | ...         | {per_table…}     | null          | null
 3 | saas:migration:backfill-default-tenant   | real    | success | ...        | ...         | {tables_touched} | null          | null
 …
```

`summary` and `errors` are JSON columns, indexed by `command` + `mode` for dashboarding.

---

## Risks Remaining

1. **Legacy `settings` table not dropped** — by design (rule: no hard-delete before production cutover). Operations must explicitly decide when to deprecate the wide-column table after confirming all callers use `Settings::current()` or `SettingsRepository::getWithFallback()`.

2. **File columns registry is curated, not exhaustive** — 25 columns across 11 tables documented in `config/saas_migration.php::file_columns`. Any file column added AFTER Phase 7 must be appended to the config before running `migrate-files` again.

3. **Parent/child edge list is curated** — `ValidateCommand::parentChildEdges()` lists 16 explicit edges. New models with parent tenant relationships must extend this list to participate in the consistency check.

4. **Central-table classification relies on `config/saas_migration.php::central_tables`** — any new SaaS-layer table added must be listed there, otherwise it will be incorrectly flagged as tenant-owned by `backfill` (a harmless but noisy false positive).

5. **Seeded `default` tenant expected** — `backfill` and `migrate-settings` require a row in `tenants` with `slug='default'` (or `id=TENANCY_DEFAULT_TENANT_ID`). Production cutover runbook must create/verify this as step 0.

---

## Before / After

```bash
# Before Phase 7 — no migration tooling
# Operations had to hand-roll INSERT/UPDATE statements for backfill,
# had no audit trail, no dry-run, no validator.

# After Phase 7 — fully scripted, audited, reversible
$ php artisan saas:migration:inventory
  └─ classifies every table
$ php artisan saas:migration:backfill-default-tenant --dry-run
  └─ prints exactly how many rows will move, per table
$ php artisan saas:migration:backfill-default-tenant
  └─ idempotent, audited
$ php artisan saas:migration:migrate-settings
  └─ sensitive keys encrypted at rest
$ php artisan saas:migration:migrate-files
  └─ copy-then-delete ordering, no overwrite, idempotent
$ php artisan saas:migration:validate
  └─ exit 0 = GO for cutover
```

---

## Deliverables

**New:**
- `database/migrations/2026_04_24_150000_create_saas_migration_runs_table.php`
- `app/Models/SaasMigrationRun.php`
- `config/saas_migration.php`
- `app/Console/Commands/Saas/Migration/BaseSaasMigrationCommand.php`
- `app/Console/Commands/Saas/Migration/InventoryCommand.php`
- `app/Console/Commands/Saas/Migration/BackfillDefaultTenantCommand.php`
- `app/Console/Commands/Saas/Migration/MigrateSettingsCommand.php`
- `app/Console/Commands/Saas/Migration/MigrateFilesCommand.php`
- `app/Console/Commands/Saas/Migration/ValidateCommand.php`
- `tests/Feature/SaasMigration/SaasMigrationTest.php`
- `PHASE7_REPORT.md` (this file)

---

## Final Verdict

| Gate | Status |
|---|---|
| Full test suite passes | ✅ **163/163** (414 assertions) |
| All 5 commands registered and functional | ✅ |
| Dry-run mode on every mutating command | ✅ (backfill, migrate-settings, migrate-files) |
| Idempotent re-runs | ✅ (tested) |
| Audit log `saas_migration_runs` populated | ✅ (tested) |
| Validation invariants checked | ✅ 4 invariants |
| Encryption-at-rest for sensitive settings | ✅ (tested with raw-column assertion) |
| File migration: safe copy-then-delete | ✅ (tested) |
| Missing-source handling | ✅ log + skip |
| Conflict handling | ✅ log + skip |
| Rollback plan documented | ✅ |
| Commit + push | ✅ |

**Verdict: GO for production cutover — the migration toolchain is complete, tested, and reversible. Ready for Phase 8 (production hardening).**
