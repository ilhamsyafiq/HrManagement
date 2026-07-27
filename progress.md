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

## Filament Admin Panel (NEW) — `/panel`

Hybrid migration: Filament for admin CRUD, legacy Blade kept for employee self-service.
Access is gated by `User::canAccessPanel()` (Admin + Super Admin only). Reachable via the
"Admin Panel (Filament)" link in the app's Settings menu.

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

## Still Partial / Not Implemented

- **Geofencing** — office CRUD works; clock-in only *flags* out-of-range, doesn't block.
- **Email** — `MAIL_MAILER=log` (no real sending configured).
- Payslip PDF download · real-time notifications · leave-balance tracking · overtime · shift scheduling.

---

## Suggested Next Steps

1. Add Filament **widgets/dashboard** stats to `/panel` (headcount, pending approvals, today's attendance).
2. Optionally retire the legacy Blade `/admin/*` screens once Filament is validated in use.
3. Enforce (or intentionally defer) geofence blocking on clock-in.
4. Configure real mail transport if email notifications are needed.
