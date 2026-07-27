# HR Management System — Progress

_Last updated: 2026-07-27_

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
| **Organization** | Departments, Claims, Announcements, Holidays, Payroll |
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
