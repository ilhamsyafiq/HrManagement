# HR Management System — Progress

_Last updated: 2026-08-04_

---

## 🎨 Batch 2026-08-04 (wave 13) — Reports page redesign (DONE, verified)

The Reports (PDF) page had 6 rainbow header buttons (primary/success/warning/gray/info/danger) crammed in the header. Redesigned `app/Filament/Pages/Reports.php` + `filament/pages/reports.blade.php`: clean header, a "Report filters" section, and an "Available reports" **card grid** (icon tile + name + description + one consistent "Open report" button per report). Each report is now a single page action opening a **tabbed modal** (Data table | PDF preview) via new `filament/reports/report-modal.blade.php` (reuses the existing data-table + pdf-preview partials). Cards render the actions via `{{ $this->{$card['action']} }}`. Verified `/panel/reports` 200, 6 buttons render, rainbow gone, blades compile.

---

## 🐛 Batch 2026-08-04 (wave 12b) — Chat scroll fix + missed-message notifications (DONE, verified)

- **Chat only showed ~3 + latest hidden:** flexbox `min-height:0` gotcha — the conversation list, messages pane, and new-chat list were flex children with `overflow-y-auto` but no `min-h-0`, so they grew to full content height and were clipped by the panel (no scroll; newest messages cut off). Added `min-h-0` to all three scroll containers. Backend already returned all conversations (verified 6/6).
- **Missed-message notifications:** widget poll now diffs unread per conversation and fires a **desktop Notification** (click → focus + open convo), a **tab-title badge** (`(N) …`), and a soft **beep** on new incoming messages — only when not actively viewing that convo. Permission requested on load + on open.

---

## 💬 Batch 2026-08-04 (wave 12) — Floating chat widget (WhatsApp-style, 1:1 + group) (DONE, verified)

New conversation-based chat powering a floating widget (bottom-right) on the Blade app; **polling every 4s**, Blade users only (admins stay on Filament). Kept separate from the legacy `messages` table.
- **Schema (migration `2026_08_04_000003`):** `conversations` (is_group, name, created_by, last_message_at), `conversation_participants` (pivot + `last_read_message_id`), `conversation_messages`. Models: `Conversation` (+`findDirect`), `ConversationMessage`; `User::conversations()`.
- **Recipients unified:** extracted `App\Services\RecipientResolver::allowedFor()` (Super Admin restriction included); `MessageController::getAllowedRecipients` now delegates to it — single source for the messages page AND chat.
- **`ChatController` (JSON):** conversations (title/last/unread), messages (`?after=` for polling, marks read), send, start (1 recipient = direct find-or-create, 2+ = group; validates against allowed set), recipients. Routes under `/chat/*` (outside `redirect.admin`).
- **Widget** (`components/chat-widget.blade.php`, Alpine via `Alpine.data`): launcher w/ unread badge, conversation list, chat bubbles (mine right/indigo, group shows sender), text input, new-chat with searchable multi-select (1 = direct, 2+ = group + name). Included in `layouts/app.blade.php` under `@auth`.
- **Verified (HTTP):** recipients exclude Super Admin; start 1:1 + group; send; supervisor sees both convos w/ unread_total=2; intern→Super Admin start = **422**; widget renders on `/dashboard`; classes compiled; migration applied; all lint clean.

---

## 🗓️ Batch 2026-08-04 (wave 11) — Flexible-days toggle on shift assignment (DONE, verified)

Create Shift Assignment required ticking Days of week — awkward for part-time (flexible time + day). Added a **"Flexible days (part-time)" toggle** (`->live()`) to `ShiftAssignmentResource` form: when on, the `days` CheckboxList is `hidden()` + `required(false)`, and `CreateShiftAssignment::handleRecordCreation` fans the shift out to **all 7 days** (`array_keys(ShiftAssignment::DAYS)`) instead of the ticked ones. Verified: flexible create → 7 assignments; `ScheduleResolver` returns a flexible schedule every day (weekend + weekday). Lint clean, create page 200.

---

## 🛠️ Batch 2026-08-04 (wave 10) — Message recipient reliability (Super Admin) (DONE, verified)

Report: "no one can message super admin". Backend was fine — `getAllowedRecipients` includes admins/super-admins for every role and a direct POST creates the message. The blocker was the **custom Alpine dropdown** from wave 9: it made selecting a recipient unreliable in the browser (no recipient chosen → nothing sent). Reverted the recipient field to a **native `<select>`** (guaranteed to submit) while **keeping the de-dup + group-by-role** improvement (each person once, not 3×). Verified: intern compose shows a "Super Admin" optgroup with the SA option, and intern→SA send returns 302 + message row created. (A searchable picker can return later via a proper tested component if desired.)

---

## 💬 Batch 2026-08-04 (wave 9) — Nicer message recipient picker (DONE, verified)

Compose-message recipient was a raw native `<select>` that listed each person **3×** (My Team / role / department optgroups) and looked unstyled. Replaced with a custom Alpine dropdown in `messages/create.blade.php`: **deduped, grouped by role, searchable**, with avatar initials + department, selected-state highlight, matching the card styling. Sets the same `receiver_id`; recipient set unchanged server-side (`getAllowedRecipients`). Recipient data passed via `@js` (`JSON.parse` — attribute-safe). Rebuilt assets. Verified `/messages/create` 200, picker present, old optgroups gone.

---

## 🐛 Batch 2026-08-04 (wave 8) — Account settings 500 fixed (DONE, verified)

`/profile` (Account settings, Blade) threw a 500: `syntax error, unexpected token "endif"`. Cause — a `@unless(...)…@endunless` directive was placed **inside an `<x-text-input>` component attribute** (email field `class`), which Blade compiles to `<?php if…endif ?>` inside the attribute string and corrupts the component output. Fixed by using a ternary echo (`{{ $canEditEmail ? '' : '…' }}`) instead. Same readonly-for-non-admins behaviour. Verified `/profile` 200 for employee + superadmin.

---

## 🎛️ Batch 2026-08-04 (wave 7) — Simplify dashboard Customize modal (DONE)

The Customize modal was cluttered (each widget = a card with a full-width "Show on dashboard" labeled toggle + 3 reorder icons). Tightened in `app/Filament/Pages/Dashboard.php`: removed `reorderableWithButtons` (drag handle only), `hiddenLabel()` on the toggle + `inline()` (bare switch, no redundant label), `collapsible(false)`, modal width `lg`→`md`, simpler copy. Each row is now name + drag handle + one switch. Lint clean; `/panel` 200. (Can go further to a plain checklist without reorder if still too much.)

---

## ✍️ Batch 2026-08-04 (wave 6) — Signature: placement-based, minimal stamp (DONE, verified)

Per request: removed the "APPROVED" text + green box + "Signed by" line, and made signing **location-based** (each document's signature spot differs).
- **Stamp is now just the signature image + a small date** at a chosen spot. `placeSignature()` maps fractional page coords (fx, fy) → mm using the page's real size (`getTemplateSize`), centres the signature on the click, clamps on-page. Comments (optional) still print on the last page. Removed the old `stampApprovalBlock` (box/APPROVED/signed-by).
- **Placement flow:** `sign()` now takes `page` + `pos_x`/`pos_y` (0–1) + `width_frac`. The Sign page (`reports/sign.blade.php`) was rewritten to a **click-to-place** UI: preloads the signer's saved signature (draw a new one optionally), pdf.js renders the doc with page nav, click on the page drops a signature marker at that spot, submit sends the fractional coords.
- **One-click removed** (it can't know the per-document spot): `quickSign` now redirects to the placement page; the "Sign"/"Sign Report" buttons in `reports/index` + `reports/show` link straight to it.
- **Verified (pdftotext):** signed PDF no longer contains `APPROVED` or `Signed by`; comments still present; placement sign POST (page/pos_x/pos_y) → report signed. Controller lint + blade compile clean.
- **Layout fix (follow-up):** the rewritten Sign page had been shipped without an asset rebuild, so its classes weren't in the CSS and the PDF (relying on the purged `overflow-auto`) rendered full-size/overflowing. Fixed: `npm run build`; made the PDF scroll box inline (`max-height:75vh; overflow:auto`); canvas `max-width:100%; height:auto`; de-stretched the signature pad. Verified classes compiled + form 200.

---

## 🔔 Batch 2026-08-04 (wave 5) — Intern Reports promoted in nav (DONE, verified)

Supervisors had **no direct link** to intern reports — the `reports.index` link rendered only for interns, so a supervisor could only reach a pending report via a notification and easily miss it. Added a **top-level "Intern Reports"** nav item for supervisors (desktop + mobile), **outside** the Manage dropdown, with a **red badge = count of reports pending their signature** (same pattern as the Messages unread badge). Gated by `isSupervisor()`. Verified: supervisor sees it with the pending count; Employee role sees nothing.

---

## 🖊️ Batch 2026-08-04 (wave 4) — Report signing actually signs (DONE, verified)

Supervisor report sign/view was broken: signatures were stamped **off-page** and one-click sign hard-failed.
- **Root cause 1 (off-page signature):** `stampSignature` converted canvas coords with `pdf_x = x*0.75` / `pdf_y = (600−y)*0.75` and FPDF's default unit is **mm** — e.g. quickSign's `x=380` → `285mm` on a 210mm-wide page, so the signature (and "APPROVED") landed off the visible page. The signed PDF looked unsigned, which also made the "View Signed" preview look broken.
- **Root cause 2 (one-click fails):** `quickSign` returned early with an error unless the signer had previously saved a signature (the `signatures` table was empty), so first-time signing never worked.
- **Fix:** rewrote stamping to place a **fixed approval block** (signature image → or a text signature when none saved → + "APPROVED" + "Signed by {name}" + date + optional comments) on the **last** page, positioned from the page's real size via `getTemplateSize` (on-page for any A4/Letter/orientation). `sign()` uses the drawn `signature_data` directly (no more fragile click-to-place coords); `quickSign()` always works — uses the saved signature image if present, else a text signature. UTF-8 → Latin-1 transliteration for FPDF core fonts. Temp PNG cleanup preserved.
- **Verified (pdftotext):** signed PDFs now contain on-page `APPROVED` / `Signed by: Supervisor User` / `Supervisor comments: …`. Manual sign (image + comments + save-as-default) and one-click sign (no stored signature) both succeed; `/reports` `/reports/{id}` `/reports/{id}/pdf` `/reports/{id}/pdf-signed` all 200 for the supervisor.

---

## 🐛 Batch 2026-08-04 (wave 3) — Form/validation fixes (DONE, verified)

- **Flexible shift couldn't save** — `shifts.start_time`/`end_time` were `NOT NULL`, so a Flexible shift (which hides those fields → null) failed at the DB and surfaced as a "required" error. Made both columns nullable (migration `2026_08_04_000002`) and added `->live()` to the `is_flexible` toggle in `ShiftResource` so the times hide + drop their `required()` rule the instant it's toggled. Verified: a flexible shift with null times now saves.
- **Claim item description forced-required** — it was `required` in 4 places (Filament `ItemsRelationManager`, `ClaimController@store` `items.*.description`, `@addItem`, and the `claims/show` Blade `required` attr) with a `NOT NULL` column. Now optional everywhere: dropped `->required()`, validators → `nullable`, column made nullable (same migration), Blade label → "(optional)". Verified: a claim item with null description saves.

---

## 🗓️ Batch 2026-08-04 (wave 2) — Monthly attendance calendar + team view (DONE, verified)

Reworked the employee attendance page from a flat table into a **monthly calendar**, and added a **team attendance matrix** for managers (leave/AL planning). Investigated the data model first (leave types/statuses, holidays, shift off-days, team links), then built.

**New: `app/Services/AttendanceCalendarService.php`** — given `(user, month)` returns a view-ready day-by-day status map + month summary. One query per dataset (attendances / approved+pending leaves / holidays incl. recurring), off-days derived from `ScheduleResolver` (null = rest day). Per-day precedence: **worked > public holiday > leave > off > absent (past scheduled) > upcoming**. Each status carries type, colour, short code (matrix) and label (tooltip).

**Personal view (`attendance/index.blade.php`)** — default is now a **month calendar** (prev/next + month picker) with colour-coded days (worked/AL/MC/Emergency/holiday/off/absent), Late/Early/WFH/OT sub-badges, a **summary strip** (worked · AL · MC · Emergency · absent · late · OT) and a legend. The old flat table is kept as a **List toggle** (`?view=list`). Dark-mode + responsive.

**Team view (`attendance/team.blade.php`, `attendance.team` route + `AttendanceController@team`)** — teammates × days matrix; each cell a colour marker (P/AL/MC/EL/IL/PH/off/✕). **Pending leaves render dashed** (approved solid) for AL planning, and a bottom **"On leave" count row** flags days where >1 person is off (amber = possible clash). Scope by role, enforced server-side: **Supervisor → direct reports; HOD → department members; Admin → everyone (+ dept filter)**. Regular staff have **no** team view (403). Route lives outside `redirect.admin` so admins reach it too. Nav link added (desktop Work dropdown + mobile) for supervisors/HODs.

**Verified:** `php -l` clean; `view:cache` compiles all blades; service smoke test (Ali, Aug & Jul 2026 — correct leading weekday, 31 days, sane status/summary); authenticated HTTP — supervisor: `/attendance` `/attendance?view=list` `/attendance/team` `/attendance/team?month=…` all **200**; employee: own `/attendance` **200**, `/attendance/team` **403** (gated). **Perf note:** team matrix calls `ScheduleResolver` per member×day (~member×31); fine for small teams, revisit with caching if a department is large.

**Asset build:** the new calendar/matrix use Tailwind arbitrary utilities (`text-[9px]`, `min-h-[92px]`, `min-w-[150px]`, …) — must `npm run build` (Apache serves built assets, no dev server) or they're purged and render at browser defaults (first pass shipped without a rebuild → giant "PEND" text + collapsed cells; fixed by rebuilding + switching cells from `aspect-[4/3]` to `min-h`).

**Follow-up — Team view opened to employees + polish + demo data:**
- **Employees can now see their own department** (read-only). `resolveTeam` = subordinates ∪ headed-dept members ∪ **own department** ∪ self; `canViewTeam` now true for anyone with a `department_id`. Nav link shows for all staff.
- **Leave-type privacy:** same-department peers see a generic **"On leave" (L, sky)** marker; managers (admin / has subordinates / HOD) and the viewer's own row still see the type (AL/MC/EL). Driven by `showLeaveType` + per-row `$ownRow`. Legend adapts.
- **Matrix polish:** table now `w-full table-fixed min-w-[1000px]` (fills width instead of hugging left), weekend columns lightly shaded in the body, member drill-down link guarded to where the personal page allows it (self / supervisor→own-intern) to avoid 403s, "(you)" tag on own row.
- **Demo data:** new `DemoTeamSeeder` (idempotent, IT-dept-scoped) — HOD + Mon–Fri roster, a full previous month + current-month-to-date of varied attendance (present/late/WFH/OT/absent), and curated overlapping leaves (approved+pending) incl. a 2-person clash on the 12th–13th + a mid-month demo holiday. Run: `php artisan db:seed --class=DemoTeamSeeder --force`. Verified: Sarah `/attendance/team` 200 + generic leave + IT scope; supervisor 200 + full detail; rebuilt assets.

**Follow-up — Calendar & Holidays enriched:** the standalone `/calendar` page was near-empty (events-only) and felt useless next to the new attendance calendar. `CalendarEventController@eventsData` now also returns a `leaves` array — the user's own approved/pending leaves, plus their team's (subordinates / headed-department members; admins = everyone) — rendered as colour chips per day in the existing JS grid (AL blue / MC rose / Emergency amber / Intern indigo; **pending = dashed**). Legend extended. Kept as a separate page (Add Event / Add Holiday / Upcoming intact). Verified via `events-data`: employee sees own AL-pending (Aug 2026); supervisor sees own + 2 subordinates' leaves (Apr 2026). Rebuilt assets.

---

## 🔧 Batch 2026-08-04 — Bug fixes + customizable admin dashboard (DONE, verified)

Investigated Attendance/Clocking, Reports/Signing, and the Filament dashboard with 3 parallel agents (file:line grounding), verified each finding against the real code (discarded 2 false positives), then implemented.

**Bug fixes (verified real):**
- **Attendance "Edited" badge never rendered** — `attendance/index.blade.php:77,80` + `admin/attendances.blade.php:124,127` read `$attendance->is_edited`, but the column is `is_manually_edited`. Added an `is_edited` accessor on `Attendance` (single choke point; both views now work).
- **Signature temp-file leak** — `ReportController::stampSignature()` did `tempnam(...) . '.png'`, orphaning the base temp file every sign. Now unlinks both `$tmpBase` and `$tmpPath` in `finally`.
- **Admins couldn't sign reports** — `reports.sign.form` / `reports.sign` / `reports.quicksign` were inside the `redirect.admin` group, so Admin/Super Admin got bounced even though `authorizeSigning()` permits them (bites when an intern has no supervisor → report falls back to admins). Moved the 3 routes out of `redirect.admin` (next to `reports.pdf`); controller gate unchanged. Confirmed registered via `route:list`.
- _False positives discarded:_ `setTimeFromTimeString()` **is** a real Carbon method; `Carbon::diffInSeconds()` returns an absolute value by default. No changes.

**Feature — per-admin dashboard customization:**
- New `users.dashboard_preferences` JSON column (migration `2026_08_04_000001`, `array` cast) — stores `{ "widgets": [ {key, visible}, ... ] }` per admin; null = default layout.
- Custom `app/Filament/Pages/Dashboard.php` replaces the vendor Dashboard (registered in `AdminPanelProvider`, `GET panel` now resolves to it). Overrides `getWidgets()` to apply each admin's visibility + order (works because the Filament widgets Blade renders in array order; newly-added widgets append as visible). Header actions: **"Customize"** (modal with a reorderable list — drag to reorder + per-widget show/hide toggle) and **"Reset"** (restore defaults). Saves to the current user.

**Verified:** all changed files `php -l` clean; migration applied (column present); `optimize:clear` OK; `route:list` shows sign routes + custom dashboard; HTTP `/login` 200, `/panel/login` 200, `/panel` 302→login. **Browser eyeball still recommended:** authenticated dashboard render + the Customize modal drag/toggle (couldn't drive an authed session headlessly).

**Env note:** the migration was briefly blocked because a standalone **MySQL 8** had taken port 3306 ahead of XAMPP's MariaDB (where `hr` lives); once MariaDB held 3306 again, migration ran. See memory note.

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
