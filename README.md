# HR Management System

A comprehensive HR Management web application built with Laravel 11, Tailwind CSS, and Alpine.js. Designed for Malaysian companies with built-in support for statutory deductions (EPF, SOCSO, EIS, PCB).

---

## Table of Contents

- [Installation](#installation)
- [Default Accounts](#default-accounts)
- [User Roles](#user-roles)
- [System Features & Process Flows](#system-features--process-flows)
  - [1. Attendance & Clocking](#1-attendance--clocking)
  - [2. Leave Management](#2-leave-management)
  - [3. Expense Claims](#3-expense-claims)
  - [4. Calendar & Holidays](#4-calendar--holidays)
  - [5. Announcements](#5-announcements)
  - [6. Messaging](#6-messaging)
  - [7. Intern Reports](#7-intern-reports)
  - [8. Employee Profile](#8-employee-profile)
  - [9. Payroll](#9-payroll)
  - [10. Admin Panel](#10-admin-panel)
- [Known Issues & Limitations](#known-issues--limitations)

---

## Installation

### Requirements

- PHP 8.2+
- Composer
- Node.js & npm
- MySQL 8.0+

### Setup Steps

```bash
# 1. Clone the repository
git clone https://github.com/ilhamsyafiq/HrManagement.git
cd HrManagement

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Copy environment file and configure
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Configure database in .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=hr_management
# DB_USERNAME=root
# DB_PASSWORD=

# 7. Run migrations and seed data
php artisan migrate:fresh --seed

# 8. Create storage symlink
php artisan storage:link

# 9. Build frontend assets
npm run build

# 10. Start the server
php artisan serve
```

---

## Default Accounts

After running seeders, these test accounts are available:

| Role | Email | Password |
|------|-------|----------|
| Super Admin | superadmin@example.com | password |
| Admin | admin@example.com | password |
| Supervisor | supervisor@example.com | password |
| Employee | employee@example.com | password |
| Intern | intern@example.com | password |

---

## User Roles

| Role | Description | Access Level |
|------|-------------|-------------|
| **Super Admin** | Full system control. Can manage all users, settings, and configurations. | Everything |
| **Admin** | Manage employees, approve leaves/claims, generate reports, manage departments. | Admin panel, approvals, reports |
| **Supervisor** | Manage subordinates and interns. First-level approver for leaves. | Approvals, team management |
| **Employee** | Regular employee. Can clock in/out, request leaves, submit claims. | Self-service features |
| **Intern** | Limited access. Must upload reports for supervisor signing. | Basic features + reports |

---

## System Features & Process Flows

### 1. Attendance & Clocking

**Who can use:** All employees, interns

**Flow:**

```
Employee opens Clock page
        |
        v
Click "Clock In" --> System records time + GPS location
        |
        v
  (Working...)
        |
        v
Click "Break In" --> Break timer starts
        |
        v
Click "Break Out" --> Break ends, duration recorded
        |
        v
Click "Clock Out" --> System records time + GPS location
        |
        v
System calculates total work hours (minus break time)
System checks for late arrival / early departure
```

**Features:**
- GPS-based location tracking on clock in/out
- Geofencing validation (if office locations configured)
- Break tracking with in/out times
- Automatic late/early leave detection based on working hours config
- WFH (Work From Home) flag
- Admin can edit attendance records with audit trail

**Admin view:** Navigate to **People > Attendances** to see all employee attendance records with filters.

---

### 2. Leave Management

**Who can use:** All employees, interns

**Leave Types:** Annual Leave (AL), Medical Leave (MC), Emergency Leave, Intern Leave

**Flow:**

```
Employee creates leave request
(Select type, dates, reason, optional supporting document)
        |
        v
Status: "Pending"
        |
        v
  +-----------+------------------+
  | Employee  |     Intern       |
  +-----------+------------------+
       |                |
       v                v
  Admin/SV          Supervisor reviews
  approves/          (First approval)
  rejects                |
       |                 v
       v          Status: "Supervisor Approved"
  Status:                |
  Approved/              v
  Rejected         Admin reviews
                   (Final approval)
                         |
                         v
                   Status: Approved/Rejected
```

**Key points:**
- Interns require two-level approval (Supervisor first, then Admin)
- Regular employees need only Admin/Supervisor approval
- Rejection requires a reason
- Approved leaves cannot be edited

**Navigation:**
- Employee: **Work > Leave**
- Admin: **People > Leaves** (view all) or **People > Approvals** (pending only)

---

### 3. Expense Claims

**Who can use:** All employees

**Flow:**

```
Employee creates a new claim
(Title, description)
        |
        v
Status: "Draft"
        |
        v
Add items to claim
(Description, amount, date, category, receipt)
        |
        v
Submit claim for approval
        |
        v
Status: "Pending"
        |
        v
Admin/Supervisor reviews
        |
   +----+----+
   |         |
   v         v
Approved   Rejected
   |       (with reason)
   v
Admin marks as "Paid"
```

**Claim Categories:** Transport, Meal, Accommodation, Office Supplies, Medical, Training, Other

**Navigation:**
- Employee: **Work > Claims**
- Admin: **Organization > Claims**

---

### 4. Calendar & Holidays

**Who can use:** All users (view), Admin (manage holidays)

**Flow:**

```
Calendar page shows monthly view
        |
   +----+----+
   |         |
   v         v
Events    Holidays
(personal)  (company-wide)
```

**Personal Events:**
- Any user can create personal events
- Types: Personal, Meeting, Deadline, Reminder, Other
- Option to notify supervisor

**Holidays (Admin only):**
- Add public/company/optional holidays
- Set as recurring (yearly)
- Edit or delete existing holidays

**Navigation:** **Organization > Calendar & Holidays** (Admin) or **Info > Calendar & Holidays** (Employee)

---

### 5. Announcements

**Who can use:** All users (view), Admin (create/manage)

**Flow:**

```
Admin creates announcement
(Title, content, priority, target audience, dates)
        |
        v
Target options:
  - All users
  - Specific department
  - Specific role
        |
        v
Announcement appears on:
  1. Announcements page (list view)
  2. Dashboard popup (if High/Urgent priority or upcoming date)
```

**Priority levels:** Low, Normal, High, Urgent

**Dashboard popup:** High/Urgent priority announcements and announcements published within the last 1 day or next 3 days automatically appear as a popup on the dashboard.

**Navigation:**
- Admin: **Organization > Announcements**
- Employee: **Info > News**

---

### 6. Messaging

**Who can use:** All authenticated users (with restrictions)

**Recipient Rules:**

| Sender Role | Can Message |
|-------------|------------|
| Employee / Intern | Their own Supervisor + Department HOD |
| Supervisor | Their subordinates + Admin users |
| Admin / Super Admin | Anyone |

**Flow:**

```
User clicks "Compose" on Messages page
        |
        v
Select recipient (filtered by role rules above)
Write subject and message body
        |
        v
Send message
        |
        v
Recipient sees unread badge in navigation
        |
        v
Recipient opens message, types reply
        |
        v
Threaded conversation continues
```

**Features:**
- Threaded replies (conversation view)
- Read/unread status with blue dot indicator
- Inbox and Sent tabs
- Unread count badge in navigation bar

**Navigation:** **Messages** link in navigation bar

---

### 7. Intern Reports

**Who can use:** Interns (upload), Supervisors (sign)

**Flow:**

```
Intern uploads report
(Title, file upload)
        |
        v
Status: "Draft"
        |
        v
Intern submits report for signing
        |
        v
Status: "Pending"
        |
        v
Supervisor reviews report
        |
   +----+----+
   |         |
   v         v
 Sign     Reject
(digital   (with comments,
signature)  intern revises)
   |
   v
Status: "Signed"
        |
        v
Intern can download signed report
```

**Navigation:** **Reports** link in navigation (visible to Interns and Supervisors)

---

### 8. Employee Profile

**Who can use:** All users (own profile), Admin (any profile)

**Sections:**
- **Personal Info:** Phone, IC Number, Date of Birth, Gender, Marital Status, Address
- **Banking Info:** Bank Name, Account Number, EPF Number, SOCSO Number, Tax Number
- **Emergency Contact:** Name, Phone, Relationship
- **Employment Info:** Job Title, Hire Date, Basic Salary
- **Documents:** Upload contracts, certificates, IDs, resumes
- **Employment History:** Track promotions, transfers, hiring records

**Navigation:**
- Settings dropdown > **My Profile**
- Admin can view any user's profile from the Users list

---

### 9. Payroll

> **Note:** Payroll is currently removed from the navigation menu. The feature exists in the codebase but is not actively linked. See [Known Issues](#known-issues--limitations).

**Flow (when enabled):**

```
Admin generates payroll for a month
        |
        v
System calculates:
  - Basic salary
  - EPF (11% employee, 12% employer)
  - SOCSO
  - EIS
  - PCB tax
        |
        v
Admin adds allowances/deductions
        |
        v
Admin approves payroll
        |
        v
Admin marks as paid
        |
        v
Employee can view payslip
```

---

### 10. Admin Panel

#### User Management

**Navigation:** People > Users

| Action | Description |
|--------|-------------|
| View Users | List all users with role, department, status |
| Create User | Add new user with name, email, role, department, supervisor assignment |
| Edit User | Update user details, change role/department |
| Delete User | Remove user (Super Admin cannot be deleted, Admin can only be deleted by Super Admin) |

#### Department Management

**Navigation:** Organization > Departments

| Action | Description |
|--------|-------------|
| View Departments | List all departments with HOD, user count |
| Add Department | Create new department with name, description, HOD assignment |
| Edit Department | Update department name, description, HOD |
| Delete Department | Remove department (must have 0 users assigned) |

#### How to Set HOD / Supervisor

**Setting a Head of Department (HOD):**
1. Go to **Organization > Departments**
2. Click Edit on the department
3. Select a user from the HOD dropdown
4. Save

**Setting a Supervisor for an employee:**
1. Go to **People > Users**
2. Click Edit on the employee
3. Select a user from the Supervisor dropdown
4. Save

#### Working Hours Configuration

**Navigation:** Settings > Working Hours

- Set default working hours (e.g., 9:00 AM - 5:30 PM)
- Set break time (e.g., 1:00 PM - 2:00 PM)
- Configure late threshold (minutes grace period)
- Configure early leave threshold
- Set custom hours for specific users

#### Office Locations (Geofencing)

**Navigation:** Settings > Geofencing

- Add office locations with GPS coordinates and radius
- System validates clock in/out location against configured offices
- Flag attendance if outside geofence radius

#### Audit Logs

**Navigation:** Settings > Audit Logs

- View all system changes (create, update, delete actions)
- Tracks: who, what, when, old values, new values
- Filter by date range

#### Reports (PDF Generation)

**Navigation:** Settings > Reports

Available report types:

| Report | Description |
|--------|-------------|
| Attendance Report | Clock in/out times, total hours, late/early flags |
| Leave Report | Leave requests with status, type, dates |
| Employee Report | Employee details by department |
| Department Report | Department summary with headcount |
| Monthly Summary | Overall monthly statistics |
| Audit Report | Audit log entries for date range |

All reports can be filtered by date range, department, and specific employee.

---

## Dark Mode

The system supports three theme modes:

- **Light** (sun icon) - Light background
- **Dark** (moon icon) - Dark background
- **System** (monitor icon) - Follows your OS preference

Click the theme toggle icon in the navigation bar to cycle through modes. Your preference is saved and persists across sessions.

---

## Known Issues & Limitations

### Resolved Issues

| Issue | Description | Status |
|-------|-------------|--------|
| **Audit Log empty** | Audit log entries are now created for user CRUD, leave approvals/rejections, attendance edits, and user deletions. | Solved |
| **PDF Report errors** | Fixed class reference from `Fpdf` to `\FPDF()` across all report generation methods. | Solved |
| **PDF time format** | Fixed to use `H:i` format instead of `H:i:s`. | Solved |
| **Attendance total hours** | `total_work_hours` is calculated on clock out and recalculated after break out. The `formatted_work_hours` accessor also calculates on-the-fly if the stored value is missing. | Solved |
| **Leave document upload** | Form has `enctype="multipart/form-data"` and controller stores file to `leaves` disk. | Solved |
| **Popup notification** | Dashboard popup now shows only once per day. Uses `localStorage` to track dismissal date. | Solved |
| **Message grouping** | Compose message form now groups recipients by Role, Department, and Same Supervisor using `<optgroup>` labels. | Solved |
| **Calendar holiday modals** | JavaScript functions `openAddHolidayModal()` and `editHolidayFromView()` are implemented. | Solved |
| **Dashboard icons** | Updated admin dashboard stat cards and quick action icons to match their labels. | Solved |
| **Attendance filtering** | Admin attendance view now has filters for Employee, Month, and Status (All/Flagged/WFH). | Solved |

### Partially Working

| Feature | Description |
|---------|-------------|
| **Payroll** | Fully coded but removed from navigation. Routes and controllers exist but feature is not linked in the UI. |
| **Geofencing** | Office location CRUD works, but actual GPS validation during clock in may not enforce blocking (only flags). |
| **Email notifications** | User account creation email template exists but mail configuration may not be set up. |

### Features Not Yet Implemented

| Feature | Description |
|---------|-------------|
| Payslip PDF download | Payslip view exists but PDF export is not implemented |
| Notification system | No real-time notifications (only dashboard popup for announcements) |
| Leave balance tracking | No automatic leave balance calculation or entitlement management |
| Overtime calculation | No overtime tracking or calculation |
| Shift management | No shift scheduling feature |

---

## Tech Stack

- **Backend:** Laravel 11.31 (PHP 8.2+)
- **Frontend:** Blade Templates, Tailwind CSS, Alpine.js
- **Database:** MySQL 8.0+
- **PDF:** FPDF library
- **Build:** Vite

---

## License

This project is proprietary software developed by WebImpian.
