# TODO

## Done
- [x] Admin Reports generate buttons — routes, `AdminController` generate* methods, and wired
      forms are all present and working (the original "non-functional" note was stale).
- [x] Migrated admin/HR side to a **Filament v3 panel** at `/panel` (12 resources).
- [x] Fixed HTTPS forcing to production-only in `AppServiceProvider`.

## Next
- [ ] Add Filament dashboard widgets (headcount, pending approvals, today's attendance).
- [ ] Enforce geofence blocking on clock-in (currently only flags out-of-range).
- [ ] Configure a real mail transport (currently `MAIL_MAILER=log`).
- [ ] Payslip PDF export.
- [ ] Consider retiring legacy Blade `/admin/*` screens once Filament is validated.
