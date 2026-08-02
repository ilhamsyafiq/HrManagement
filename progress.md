# HR Management System — Progress

_Last updated: 2026-08-02_

---

## 🔒🔧 Batch 2026-08-02 (wave 2) — Pre-deploy security audit + feature wave (DONE, pending QA)

**Security audit** (10 parallel agents; 5 completed pre-limit incl. all critical dims):
- **SQL injection: CLEAN** — all Eloquent/parameterized; the one `whereRaw` uses `?` bindings; `orderByRaw` constant. Nothing to fix.
- **Critical authz fixes applied:** `AttendanceController@edit` ownership check; `ReportController` show/edit/update/destroy + download/preview gated (`authorizeView`/`authorizeManage`); `EmployeeProfileController@update` strips salary/job/hire for non-admins; profile photo `mimes` (no SVG).
- **Uploads moved to PRIVATE disk** (leave medical, claim receipts, employee ID/contract docs) + served via authorized routes (`leave.document`, `claims.receipt`, `employee-profile.document.download`); Filament FileUpload `acceptedFileTypes`/`maxSize`; NotificationController open-redirect fixed. (Pre-existing public files stay public until re-uploaded.)
- **Announcement attachments:** SVG/HTML blocked via `saveUploadedFileAttachmentsUsing` MIME allowlist.
- **Deploy checklist (NOT applied — would break local dev):** `.env` → `APP_DEBUG=false`, `APP_ENV=production`, `SESSION_SECURE_COOKIE=true`; disable open self-registration (`routes/auth.php`); `composer update` (fpdi/laravel advisories) + `composer install --no-dev`.

**Feature wave (7 agents + main-thread):**
- **Supervisor sign FIXED** — root cause was alpha-PNG crashing FPDF (2.3GB fatal). Now GD-flattened; added **one-click sign** (stored reusable signature in new `signatures` table + "APPROVED" stamp) and a popup PDF review before signing. (`reports.quicksign`)
- **Part-time employment type** — `users.employment_type`; no late/early tracking, paid by hours, **no AL/MC/EL** (LeaveBalanceService returns empty).
- **Intern profile fields** — university, academic_supervisor_name/contact, course, internship_weeks (UserResource "Internship Details", shown only for interns).
- **Impersonation** — native (no package), Super-Admin only, can't impersonate admins; amber "Return to your account" banner. (`impersonate.start`/`stop` + UserResource action)
- **Admin dashboard redesign** — 4 widgets polished + 6 new (Today attendance, 30-day rate, pending approvals, headcount by role, leaves by type, upcoming leaves); registered in AdminPanelProvider. (Fixed a fatal: `PendingApprovalsOverview::$heading` must be non-static.)
- **Bug report / feedback tool** (temporary) — `bug_reports` table + form for all users + Super-Admin-only Filament resource.
- **Geolocation disable bug FIXED** — deactivating all offices now truly disables geofencing (was falling back to a hardcoded config office).
- **Announcement image render FIXED** — RichEditor HTML now renders (was escaped/showing as text), sanitized via allowlist helper.
- **Email locked** in account settings (regular users: name+password only; email admin-managed).
- **Shift form** hides start/end time when Flexible is toggled.

New migrations run: `signatures`, `add_parttime_and_intern_fields_to_users`, `bug_reports`. Verified: all `app/` lints clean; 3 migrations applied; all 10 dashboard widgets execute without error; routes register; `/login` `/panel/login` 200. Working-hours vs shift: keep both (WorkingHours = fallback + thresholds).

**Follow-ups (also done):**
- **Impersonation opened to Admins** (block only self + other Super Admins); banner now also renders inside the Filament panel (render hook) so an impersonated Admin can return.
- **Password change for Super Admin/Admin** — enabled Filament account page (`->profile()`) at `/panel/profile` (user menu).
- **Shift-assignment table cleanup** — new **Hours** column (times / `Flexible` / split segments), weekend-colored Day badges, `shift.segments` eager-loaded.
- **Report data-table preview** — new `ReportDataService` + each of the 6 reports on the Reports (PDF) page is now a dropdown with **View Data** (on-screen table of the rows, respects filters) + **Preview PDF**. All 6 data methods smoke-tested OK.

---

## 🔧 Batch 2026-08-02 — Multi-portal fixes & upgrades (DONE, pending user QA)

Investigated with 5 parallel agents (file:line grounding), then implemented in 4 disjoint-file waves + integration. All PHP `php -l` clean; 2 migrations run; caches cleared; routes register; `/login` `/panel/login` 200, `/dashboard` 302.

### Superadmin / Admin
- ✅ **Shift concepts clarified (NOT redundant)** — Shift = reusable time template; ShiftAssignment = user×weekday roster row; ShiftRoster = read-only grid. Documented, nothing merged.
- ✅ **Split shifts + flexible hours** — new `shift_segments` table (many ordered work blocks per shift, e.g. 08:00–13:00 + 20:00–23:30) + `shifts.is_flexible`. `ScheduleResolver::forUser` now emits `segments[]` + `is_flexible` (start/end = first-seg-start/last-seg-end for back-compat). **Flexible = NO late/early tracking** (guarded in `AttendanceService` clockIn/clockOut). Auto-clockout snaps to last segment end. `paidHours` sums segments (overnight-aware). Filament `ShiftResource`: `is_flexible` Toggle + segments `Repeater`. Fully additive/back-compatible (no segments = old single-span behaviour).
- ✅ **Nav: Settings group pinned to bottom** — `->navigationGroups(['People','Organization','Settings'])` in `AdminPanelProvider`.
- ✅ **Report PDF preview (admin)** — Filament `DocumentResource` table + `ViewDocument` header actions "Preview PDF"/"Preview Signed" open a modal iframe (`filament/reports/pdf-preview.blade.php`) with a **Print + Download toolbar** (view/print before downloading). New inline routes `reports.pdf` / `reports.pdf.signed` (outside `redirect.admin` so admins can load them; `authorizeView` gate; `?download=1` forces attachment since `reports.download` is admin-blocked).
- ✅ **Removed dashboard "Sign out" card** — was Filament default `AccountWidget`; `->widgets([])`. Sign-out remains in top-right user menu.
- ✅ **Reports (PDF) page = preview-first** — the 6 stat-report buttons (`app/Filament/Pages/Reports.php`) no longer auto-download; each opens a preview modal (view first) with **Print + Download** inside. New `AdminReportPdfController@show` + route `admin.reports.pdf` streams inline (`?download=1` = attachment), admin-gated. Reuses `filament/reports/pdf-preview.blade.php`.
- ✅ **Per-employee AL/MC/Emergency** — nullable `al_entitlement`/`mc_entitlement`/`emergency_entitlement` on users; `LeaveBalanceService` resolves per-user override → config default (single choke point → all displays inherit); admin-editable in `UserResource` "Leave Entitlements (override)" section.

### General
- ✅ **Table filters** — added to the only two resources lacking them: `DepartmentResource` (HOD filter), `RoleResource` (role-name filter). Other 13 resources already had filters.

### Employee
- ✅ **Messaging who-can-message logic** — employees/interns may now message: direct supervisor + department HOD + all Admin/Super Admin (HR) + teammates (same supervisor and/or same department). Enforced server-side via `getAllowedRecipients` (store re-validates).
- ✅ **Announcement per-day dismiss** — intern dashboard popup now uses the same localStorage per-day dismiss as the employee dashboard (dismiss lasts until next calendar day).

### Supervisor
- ✅ **Report-submit notify fixed** — `ReportController::submit` notified via null `supervisor_id` silently. Now: notifies supervisor if set; else notifies all Admins + logs a warning (report never lost). Root cause = interns without assigned supervisor.
- ✅ **View report = PDF modal** — `reports/index` + `reports/show` "Preview"/"View Signed" buttons open Alpine modal iframe (`reports.pdf`). Alpine confirmed loaded via `<x-app-layout>`→app.js.
- ✅ **Bulk / grouped messaging** — supervisors get a "Send to a group" card on compose: My Subordinates / My Interns / My Employees (live counts). `MessageController::bulkStore` fans out one message + notification per member in a transaction; `receiver_ids[]` intersected with allowed set.

### Intern
- ✅ **View report = PDF modal** — same `reports/index`+`show` preview modal (intern authorized as owner).

**New migrations run:** `2026_08_02_000001_create_shift_segments_and_flexible`, `2026_08_02_000002_add_leave_entitlements_to_users`.

**Setup note:** Vite dev server must run for CSS (`npm run dev`), or `npm run build` for static assets. `APP_URL` corrected to `http://localhost:8080`.

**Known minor:** ShiftRoster blade looks up Shift by name to show segment times (page class passes only the name) — fine for unique names; could be tightened later.

---

## 🔧 Batch 2026-07-28 — Role fixes & upgrades (IN PROGRESS)

Cross-cutting batch requested across Superadmin/General/Employee/Supervisor. Investigated with parallel agents (root causes + file:line), then implemented in partitioned waves. Legend: ✅ done · 🚧 in progress · ⬜ pending.

**Prior sub-work (shift assignment):** ✅ assignments converted per-**date** → per-**day-of-week** (recurring; off day = no row, differs per employee); shifts gained optional unpaid break; Friday-Male (6.5h)/Friday-Female (6h)/Normal (7.5h)/Night (8h, overnight-aware) seeded; `ScheduleResolver` resolves schedule per date and feeds attendance late/early/paid-hours.

### Superadmin
- ⏸️ **Payroll module HIDDEN (deferred)** — feature-flagged OFF behind `config('hr.payroll_enabled')` (`HR_PAYROLL_ENABLED`, default false). Too complex; needs finance & HR discussion first. When off: Filament `PayrollResource` hidden from nav + `canAccess/canViewAny` deny (403); Blade `/payroll*` routes not registered; employee "Payslip" nav links hidden. Models/migrations/`PayrollCalculator` untouched. **Re-enable:** `HR_PAYROLL_ENABLED=true` + `php artisan optimize:clear`.
  - _Deferred recalibration scope (when revived):_ official EPF/SOCSO/EIS **bracket tables** (not flat %), **manual PCB**, **monthly + daily/hourly** basis (`employment_type` + `daily_rate`/`hourly_rate`), **no proration** (full monthly basic), OT from approved requests. Consolidate duplicated calc (`PayrollCalculator::calculate()` + `Payroll::calculateTotals()`).
- ✅ **Shift view** — read-only weekly grid `/panel/shift-roster` (employees × Sun–Sat). `app/Filament/Pages/ShiftRoster.php` + view.
- ✅ **Report PDF redirect** — reports moved into Filament `/panel/reports`; nav no longer jumps to `/admin/reports`. `app/Services/ReportPdfService.php`, `app/Filament/Pages/Reports.php` + view; `AdminController` delegates; `AdminPanelProvider` NavigationItem removed. Old routes still work.

### General / Employee
- ✅ **Attendance auto clock-out** — forgotten clock-out snapped to scheduled shift end via `ScheduleResolver` (overnight-aware + `hr.auto_clockout.grace_minutes`, default 120; falls back to `clock_in + standard_daily_hours` when no schedule). Migration (`is_auto_clocked_out`, `auto_clock_out_note`) ✅, `app/Console/Commands/CloseForgottenClockOuts.php` (`attendance:auto-clockout`, has `--dry-run`) ✅ closes open breaks + recomputes paid hours + notifies employee, scheduled every 5 min in `routes/console.php` ✅. Command registers; `php -l` clean. **Still to run once DB is up:** `php artisan migrate` (migration `2026_07_28_000003` pending) + Task Scheduler task for `schedule:run`.
- ⬜ **Overtime decoupled** — new standalone **approved OT request** model (dated, type continued/night, supervisor/admin approved); payroll sums only approved OT; neutralize `Attendance::getOvertimeHoursAttribute`.
- ✅ **Announcement image error** — Filament `RichEditor` given explicit `fileAttachmentsDisk('public')`/`Directory('announcements')`/`Visibility('public')`.
- ✅ **Remove delete-account** — profile delete card + `profile.destroy` route + `ProfileController::destroy()` + partial removed. Admin deletion unaffected.
- ✅ **Quick actions under clock** — moved directly under hero clock on `dashboard.blade.php` + `intern/dashboard.blade.php` (IDs preserved).
- ✅ **Simplify attendance history** — `attendance/index.blade.php` → Date/Clock In/Clock Out/Total Hours/Status; removed dup badges, inline addresses, OT column (folded into Status), breaks sub-row (inline count), edit modal; added empty state.

### Supervisor
- ✅ **Notify supervisor on report submit** — `ReportController::submit()` notifies supervisor via `SystemNotification`.
- ✅ **Sign-form fixed** — removed fatal top-level `return;` JS syntax error in `reports/sign.blade.php` (was killing whole script → view/sign/edit dead); created missing `reports/edit.blade.php`; signed PDF now embeds drawn signature as GD-flattened image (`ReportController::sign()`).

**Setup note:** auto clock-out needs a Windows Task Scheduler task running `php artisan schedule:run` every minute from the project dir. No `migrate:fresh` (live data) — additive migrations only.

---


Laravel 11 HR Management app for Malaysian companies (EPF, SOCSO, EIS, PCB support).
Stack: Laravel 11.31 · PHP 8.2+ · Blade + Tailwind + Alpine.js · MySQL/MariaDB · FPDF · Vite
· **Filament v3 admin panel (new)**.

---

## Overall Status

Core system is feature-complete. The admin/HR side has now been **migrated to a Filament v3
panel** (hybrid approach), while employee self-service stays in the existing Blade UI.

- **21 models**, **17 controllers**, **29 migrations**, legacy routes under `/admin/*`
- **Filament panel at `/panel`** — 12 resources, restricted to Admin / Super Admin
- **5 roles**: Super Admin, Admin, Supervisor, Employee, Intern

---

## Running Locally & Access URLs

Project lives at `C:\xampp\htdocs\HrManagement` (moved out of OneDrive for speed).
Served by **Apache on port 8080** (concurrency + opcache); base: **`http://localhost:8080`**.
Other XAMPP sites on port 80 are unaffected. (`php artisan serve` on :8000 is optional/legacy.)

| Who | Login URL | Lands on | UI |
|-----|-----------|----------|-----|
| **Regular users** (Employee, Intern, Supervisor) | `http://localhost:8080/login` | `/dashboard` | Blade self-service app |
| **Admin / Super Admin** | `http://localhost:8080/panel` (or `/login`) | `/panel` (Filament) | Filament admin panel |

> Admins hitting the old `/admin/*` pages are auto-redirected to Filament (`RedirectAdminsToFilament`).
> Supervisors cannot enter `/panel` — they keep the legacy admin views.

**Seeded accounts** (all password `password`):

| Role | Email |
|------|-------|
| Super Admin | superadmin@example.com |
| Admin | admin@example.com |
| Supervisor | supervisor@example.com, sarah@example.com |
| Employee | employee@example.com, ali@example.com, meiling@example.com, raj@example.com, nurul@example.com, david@example.com |
| Intern | intern@example.com |

---

## Filament Admin Panel (NEW) — `/panel`

Single admin panel: Filament for all admin CRUD; legacy Blade kept only for employee
self-service (and Supervisors). Access is gated by `User::canAccessPanel()` (Admin +
Super Admin only).

**Consolidation (single panel):** `RedirectAdminsToFilament` middleware redirects Admin /
Super Admin from the legacy `/admin/*` GET pages to their Filament equivalents, so admins
use one panel only. Supervisors (no Filament access) keep the legacy admin UI. POST/PUT/DELETE
handlers and the PDF Reports page are untouched; Reports is linked from the Filament nav.

**Dashboard:** Filament dashboard replicates the old one — `StatsOverview` (employees,
present today, pending leaves, attendance records) + charts: `AttendanceTrendChart` (6-month
line), `LeaveStatusChart` (doughnut), `DepartmentHeadcountChart` (bar).

| Nav group | Resources |
|-----------|-----------|
| **People** | Users, Attendances, Leaves |
| **Organization** | Departments, Claims, Announcements, Holidays, ~~Payroll~~ (hidden — see feature flag) |
| **Settings** | Working Hours, Office Locations, Audit Logs (read-only), Roles |

Highlights:
- **Leaves / Claims** — one-click Approve / Reject (with reason) / Mark-Paid workflow actions.
- **Payroll** — RM (`money('MYR')`) fields, statutory sections, + PayrollItems relation manager with sum.
- **Audit Logs** — genuinely read-only (view only), old→new diff, date-range filter.
- **Users** — password-safe edit (blank password preserves existing), role/dept/supervisor selects.

Verification: 49 Filament files pass `php -l`; all 12 resource routes register; smoke test 302→login (healthy); no collision with legacy `/admin/*`.

---

## Legacy Modules (Blade) — still live

| Module | Status | Notes |
|--------|--------|-------|
| Attendance & Clocking | ✅ | GPS, break in/out, late/early detection, WFH, admin edit + audit |
| Leave Management | ✅ | 2-level approval for interns; document upload |
| Expense Claims | ✅ | Draft→submit→approve→paid; categories & receipts |
| Calendar & Holidays | ✅ | Personal events + admin holidays |
| Announcements | ✅ | Priority, targeting, dashboard popup |
| Messaging | ✅ | Threaded, role-based recipients, unread badge |
| Intern Reports | ✅ | Upload→sign/reject→download |
| Employee Profile | ✅ | Personal/banking/emergency/employment + documents |
| Admin Reports (PDF) | ✅ | 6 FPDF reports — routes + `AdminController` methods + wired forms all present |
| Admin Panel (Blade) | ✅ | Superseded by Filament `/panel` but still functional |

---

## Fixes applied this round

- **HTTPS scheme** — `AppServiceProvider::boot()` now forces HTTPS **only in production**
  (`app()->environment('production')`), so local http dev works and prod stays secure.
- **Admin Reports** — confirmed already fully wired (the old `TODO.md` was stale).
- **DB connectivity** — was a stale cached config; `optimize:clear` resolved it. MariaDB `hr` online.

---

## Multi-agent workflow (`.claude/agents/`)

Three project subagents were added for parallel specialised work:
- `backend-dev` — Laravel controllers/models/migrations/routes/payroll/FPDF/Filament logic
- `frontend-dev` — Blade + Tailwind + Alpine, dark mode, responsive (legacy UI)
- `design-ui` — Filament resources & admin UX

(These auto-load in new Claude Code sessions.)

---

## Recently Added (feature round)

- **Payslip PDF** — `PayrollController@downloadPayslip` (FPDF), owner/admin-guarded, download buttons in payroll views.
- **Leave balance** — `LeaveBalanceService` + `config/hr.php` entitlements (AL 14 / MC 14 / Emergency 7, configurable); shown on dashboard + leave page.
- **Overtime** — `Attendance` accessors (`overtime_hours`, `formatted_overtime`) derived from WorkingHour config (fallback 8h/day); shown on attendance + dashboard.
- **Shift management** — `Shift` model + migration + Filament `ShiftResource` (Settings), assignable to users.
- **Notification center** — DB notifications (`SystemNotification`), `/notifications` page + nav bell badge; triggers on leave approve/reject, claim approve/reject/paid, new message/reply.
- **Report-signing guard** — `ReportController@sign/showSignForm` now restricted to the owner's supervisor or admins (was unguarded).
- **Dark mode** — completed `dark:` variants across all regular-user pages; N+1 fixes on dashboard/messages/calendar.

### Bulk round 2 (admin↔user parity + payroll engine)
- **Payroll engine** — `PayrollCalculator` + `config/payroll.php` (EPF/SOCSO/EIS/PCB statutory, configurable) + OT; "Generate payroll" action in Filament `ListPayrolls` (per-month, skips existing). Payroll stays **admin/HR viewable** (no restriction).
- **Shift roster** — `ShiftAssignment` model + migration + Filament `ShiftAssignmentResource` (per-day assignment). Users see "My Shift" on dashboard/profile.
- **Intern reports (admin)** — read-only Filament `DocumentResource`.
- **Admin parity** — Overtime column on `AttendanceResource`; per-employee Leave Balance + Shift shown on `UserResource`.
- **Email channel** — `SystemNotification` now supports `mail` (OFF until `HR_EMAIL_NOTIFICATIONS=true` + SMTP configured).
- **Employee nav** — "Payslip" link added (desktop + mobile).

## Still Partial / Not Implemented

- **Geofencing** — office CRUD + clock-in *flagging* works. Hard blocking + map is **intentionally deferred** (would need a paid maps/geocoding API). Not planned.
- **Email / real-time** — `MAIL_MAILER=log` (no real sending); notifications are DB-backed (no WebSocket push).
- Leave entitlements are flat defaults in `config/hr.php` (no per-employee / tenure-based rules yet).

---

## Suggested Next Steps

1. Payroll auto-generate (from attendance + OT) + statutory calc + email payslip.
2. Real mail transport; optionally broadcast/WebSocket for live notifications.
3. Filament per-resource policies (restrict Roles/Payroll to Super Admin).
4. Per-employee leave entitlements (tenure-based) if flat defaults aren't enough.
5. Optionally retire the legacy Blade `/admin/*` screens once Filament is validated.

> Geofencing hard-blocking is out of scope (paid API) — do not re-propose.
