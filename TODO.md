# TODO: Make Admin Reports Generate Buttons Work

## Current Status
- Admin reports page has 6 generate buttons that are non-functional
- Need to implement PDF generation for each report type

## Tasks
- [ ] Add POST routes for report generation in routes/web.php
- [ ] Implement generateAttendanceReport method in AdminController
- [ ] Implement generateLeaveReport method in AdminController
- [ ] Implement generateEmployeeReport method in AdminController
- [ ] Implement generateDepartmentReport method in AdminController
- [ ] Implement generateMonthlySummaryReport method in AdminController
- [ ] Implement generateAuditReport method in AdminController
- [ ] Update admin/reports.blade.php to make buttons submit forms
- [ ] Test PDF generation for each report type
