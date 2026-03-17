<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\BreakRecord;
use App\Models\CalendarEvent;
use App\Models\Claim;
use App\Models\ClaimItem;
use App\Models\EmployeeProfile;
use App\Models\EmploymentHistory;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Message;
use App\Models\User;
use App\Models\WorkingHour;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $employeeRoleId = \App\Models\Role::where('name', 'Employee')->first()->id;
        $departments = \App\Models\Department::all();
        $itDept = $departments->where('name', 'IT')->first()->id ?? $departments->first()->id;
        $hrDept = $departments->where('name', 'HR')->first()->id ?? $departments->first()->id;
        $finDept = $departments->where('name', 'Finance')->first()->id ?? $departments->first()->id;
        $mktDept = $departments->where('name', 'Marketing')->first()->id ?? $departments->first()->id;

        // Create additional employees across departments
        $extraEmployees = [
            ['name' => 'Sarah Ahmad', 'email' => 'sarah@example.com', 'role_id' => $employeeRoleId, 'department_id' => $itDept],
            ['name' => 'Ali bin Hassan', 'email' => 'ali@example.com', 'role_id' => $employeeRoleId, 'department_id' => $hrDept],
            ['name' => 'Mei Ling Tan', 'email' => 'meiling@example.com', 'role_id' => $employeeRoleId, 'department_id' => $finDept],
            ['name' => 'Raj Kumar', 'email' => 'raj@example.com', 'role_id' => $employeeRoleId, 'department_id' => $mktDept],
            ['name' => 'Nurul Aisyah', 'email' => 'nurul@example.com', 'role_id' => $employeeRoleId, 'department_id' => $itDept],
            ['name' => 'David Wong', 'email' => 'david@example.com', 'role_id' => $employeeRoleId, 'department_id' => $finDept],
        ];

        foreach ($extraEmployees as $emp) {
            User::firstOrCreate(
                ['email' => $emp['email']],
                array_merge($emp, ['password' => bcrypt('password')])
            );
        }

        $nonAdminRoleIds = \App\Models\Role::whereIn('name', ['Supervisor', 'Employee', 'Intern'])->pluck('id');
        $allEmployees = User::whereIn('role_id', $nonAdminRoleIds)->get();

        // Create employee profiles
        $profiles = [
            ['phone' => '012-3456789', 'ic_number' => '900101-14-5678', 'gender' => 'Male', 'date_of_birth' => '1990-01-01', 'marital_status' => 'Single', 'address' => '123 Jalan Bukit Bintang', 'city' => 'Kuala Lumpur', 'state' => 'WP KL', 'postcode' => '50200', 'job_title' => 'Software Engineer', 'hire_date' => '2024-01-15', 'basic_salary' => 5000, 'bank_name' => 'Maybank', 'bank_account_number' => '1234567890', 'epf_number' => 'EPF-001', 'emergency_contact_name' => 'Ahmad', 'emergency_contact_phone' => '013-1111111', 'emergency_contact_relationship' => 'Father'],
            ['phone' => '013-9876543', 'ic_number' => '920505-10-1234', 'gender' => 'Female', 'date_of_birth' => '1992-05-05', 'marital_status' => 'Married', 'address' => '45 Taman Sri Hartamas', 'city' => 'Kuala Lumpur', 'state' => 'WP KL', 'postcode' => '50480', 'job_title' => 'HR Executive', 'hire_date' => '2023-06-01', 'basic_salary' => 4500, 'bank_name' => 'CIMB', 'bank_account_number' => '9876543210', 'epf_number' => 'EPF-002', 'emergency_contact_name' => 'Lim', 'emergency_contact_phone' => '014-2222222', 'emergency_contact_relationship' => 'Spouse'],
            ['phone' => '011-5555555', 'ic_number' => '950815-08-7890', 'gender' => 'Female', 'date_of_birth' => '1995-08-15', 'marital_status' => 'Single', 'address' => '78 Jalan Ampang', 'city' => 'Kuala Lumpur', 'state' => 'WP KL', 'postcode' => '50450', 'job_title' => 'Accountant', 'hire_date' => '2024-03-01', 'basic_salary' => 4800, 'bank_name' => 'RHB', 'bank_account_number' => '5555555555', 'epf_number' => 'EPF-003'],
            ['phone' => '017-7777777', 'ic_number' => '880220-14-3456', 'gender' => 'Male', 'date_of_birth' => '1988-02-20', 'marital_status' => 'Married', 'address' => '99 Bangsar South', 'city' => 'Kuala Lumpur', 'state' => 'WP KL', 'postcode' => '59200', 'job_title' => 'Marketing Manager', 'hire_date' => '2022-09-01', 'basic_salary' => 7000, 'bank_name' => 'Public Bank', 'bank_account_number' => '7777777777', 'epf_number' => 'EPF-004'],
            ['phone' => '019-8888888', 'ic_number' => '970701-01-5678', 'gender' => 'Female', 'date_of_birth' => '1997-07-01', 'marital_status' => 'Single', 'address' => '12 Jalan Damansara', 'city' => 'Petaling Jaya', 'state' => 'Selangor', 'postcode' => '47400', 'job_title' => 'IT Support', 'hire_date' => '2025-01-10', 'basic_salary' => 3800, 'bank_name' => 'Maybank', 'bank_account_number' => '8888888888', 'epf_number' => 'EPF-005'],
        ];

        foreach ($allEmployees as $index => $emp) {
            $profileData = $profiles[$index % count($profiles)];
            EmployeeProfile::firstOrCreate(
                ['user_id' => $emp->id],
                array_merge($profileData, ['user_id' => $emp->id])
            );
        }

        // Create employment history for some employees
        $histories = [
            ['action' => 'Hired', 'position' => 'Junior Developer', 'department' => 'IT', 'salary' => 3500, 'effective_date' => '2024-01-15'],
            ['action' => 'Promoted', 'position' => 'Software Engineer', 'department' => 'IT', 'salary' => 5000, 'effective_date' => '2025-06-01', 'remarks' => 'Excellent performance in Q1 2025'],
        ];

        $adminUser = User::whereHas('role', fn($q) => $q->whereIn('name', ['Super Admin', 'Admin']))->first();
        $adminId = $adminUser?->id;

        foreach ($allEmployees->take(3) as $emp) {
            foreach ($histories as $h) {
                EmploymentHistory::firstOrCreate(
                    ['user_id' => $emp->id, 'action' => $h['action'], 'effective_date' => $h['effective_date']],
                    array_merge($h, ['user_id' => $emp->id, 'performed_by' => $adminId])
                );
            }
        }

        // Create default working hours
        WorkingHour::firstOrCreate(
            ['is_default' => true],
            [
                'work_start' => '09:00:00',
                'work_end' => '17:30:00',
                'break_start' => '13:00:00',
                'break_end' => '14:00:00',
                'late_threshold_minutes' => 15,
                'early_leave_threshold_minutes' => 15,
                'is_default' => true,
            ]
        );

        // Create attendance records for last 30 days with late/early flags
        foreach ($allEmployees as $emp) {
            for ($i = 30; $i >= 1; $i--) {
                $date = Carbon::now('Asia/Kuala_Lumpur')->subDays($i);
                if ($date->isWeekend()) continue;

                // Skip some days randomly (simulate absences)
                if (rand(1, 10) > 8) continue;

                $clockInHour = rand(7, 10);
                $clockInMin = rand(0, 59);
                $clockIn = $date->copy()->setTime($clockInHour, $clockInMin);
                $clockOut = rand(1, 10) > 2 ? $date->copy()->setTime(rand(16, 19), rand(0, 59)) : null;

                // Calculate late flag (>15 min after 9:00)
                $isLate = false;
                $lateMinutes = 0;
                $scheduledStart = $date->copy()->setTime(9, 0);
                $minutesLate = max(0, $clockIn->diffInMinutes($scheduledStart, false) * -1);
                if ($minutesLate > 15) {
                    $isLate = true;
                    $lateMinutes = $minutesLate;
                }

                // Calculate early leave flag (>15 min before 17:30)
                $isEarlyLeave = false;
                $earlyLeaveMinutes = 0;
                if ($clockOut) {
                    $scheduledEnd = $date->copy()->setTime(17, 30);
                    $minutesEarly = max(0, $scheduledEnd->diffInMinutes($clockOut, false));
                    if ($minutesEarly > 15) {
                        $isEarlyLeave = true;
                        $earlyLeaveMinutes = $minutesEarly;
                    }
                }

                // Random WFH
                $isWfh = rand(1, 10) === 1;

                $attendance = Attendance::firstOrCreate(
                    ['user_id' => $emp->id, 'date' => $date->toDateString()],
                    [
                        'user_id' => $emp->id,
                        'date' => $date->toDateString(),
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'is_late' => $isLate,
                        'late_minutes' => $lateMinutes,
                        'is_early_leave' => $isEarlyLeave,
                        'early_leave_minutes' => $earlyLeaveMinutes,
                        'is_wfh' => $isWfh,
                    ]
                );

                // Add a break for some days
                if ($clockOut && rand(1, 3) === 1) {
                    BreakRecord::firstOrCreate(
                        ['attendance_id' => $attendance->id],
                        [
                            'attendance_id' => $attendance->id,
                            'break_in' => $date->copy()->setTime(13, 0),
                            'break_out' => $date->copy()->setTime(14, 0),
                        ]
                    );
                }
            }
        }

        // Create leave records
        $leaveTypes = ['AL', 'MC', 'Emergency'];
        $statuses = ['Pending', 'Approved', 'Rejected'];

        foreach ($allEmployees->take(6) as $emp) {
            for ($i = 0; $i < rand(1, 3); $i++) {
                $startDate = Carbon::now()->addDays(rand(-10, 30));
                $endDate = $startDate->copy()->addDays(rand(1, 3));
                $status = $statuses[array_rand($statuses)];

                Leave::firstOrCreate(
                    ['user_id' => $emp->id, 'start_date' => $startDate->toDateString()],
                    [
                        'user_id' => $emp->id,
                        'type' => $leaveTypes[array_rand($leaveTypes)],
                        'start_date' => $startDate->toDateString(),
                        'end_date' => $endDate->toDateString(),
                        'reason' => 'Test leave - ' . fake()->sentence(3),
                        'status' => $status,
                        'approved_by' => $status !== 'Pending' ? $adminId : null,
                    ]
                );
            }
        }

        // Create payroll records for last 2 months
        foreach ($allEmployees as $emp) {
            $profile = $emp->profile;
            $basicSalary = $profile?->basic_salary ?? 3000;

            for ($m = 2; $m >= 1; $m--) {
                $month = Carbon::now('Asia/Kuala_Lumpur')->subMonths($m)->format('Y-m');

                $payroll = Payroll::firstOrCreate(
                    ['user_id' => $emp->id, 'month' => $month],
                    [
                        'user_id' => $emp->id,
                        'month' => $month,
                        'basic_salary' => $basicSalary,
                        'status' => $m === 2 ? 'Paid' : 'Approved',
                        'payment_date' => $m === 2 ? Carbon::parse($month . '-28') : null,
                        'created_by' => $adminId,
                        'approved_by' => $adminId,
                    ]
                );

                // Add some allowances
                PayrollItem::firstOrCreate(
                    ['payroll_id' => $payroll->id, 'name' => 'Transport Allowance'],
                    ['payroll_id' => $payroll->id, 'type' => 'Allowance', 'name' => 'Transport Allowance', 'amount' => 200]
                );
                PayrollItem::firstOrCreate(
                    ['payroll_id' => $payroll->id, 'name' => 'Meal Allowance'],
                    ['payroll_id' => $payroll->id, 'type' => 'Allowance', 'name' => 'Meal Allowance', 'amount' => 150]
                );

                $payroll->calculateTotals();
            }
        }

        // Create announcements
        $announcementData = [
            [
                'title' => 'Office Closure - Hari Raya Aidilfitri',
                'content' => 'Please be informed that the office will be closed on 30 March - 1 April 2026 in conjunction with Hari Raya Aidilfitri. Wishing all Muslim colleagues Selamat Hari Raya!',
                'priority' => 'High',
                'target' => 'All',
                'publish_date' => now()->subDays(2)->toDateString(),
                'is_active' => true,
            ],
            [
                'title' => 'New Leave Policy Update',
                'content' => 'Starting from April 2026, all employees are entitled to 2 additional wellness leave days per year. Please refer to the HR handbook for more details.',
                'priority' => 'Normal',
                'target' => 'All',
                'publish_date' => now()->subDays(5)->toDateString(),
                'is_active' => true,
            ],
            [
                'title' => 'IT System Maintenance',
                'content' => 'The HR system will undergo scheduled maintenance this Saturday from 10 PM to 2 AM. Please save your work before then.',
                'priority' => 'Urgent',
                'target' => 'All',
                'publish_date' => now()->toDateString(),
                'expiry_date' => now()->addDays(3)->toDateString(),
                'is_active' => true,
            ],
            [
                'title' => 'Team Building Activity - IT Department',
                'content' => 'IT Department team building will be held on 25 April 2026 at Sunway Lagoon. Please confirm your attendance with your supervisor.',
                'priority' => 'Normal',
                'target' => 'Department',
                'department_id' => $itDept,
                'publish_date' => now()->subDay()->toDateString(),
                'is_active' => true,
            ],
            [
                'title' => 'Intern Orientation Session',
                'content' => 'All new interns are required to attend the orientation session on the first Monday of each month. Please bring your identification documents.',
                'priority' => 'Normal',
                'target' => 'Role',
                'target_role' => 'Intern',
                'publish_date' => now()->subDays(10)->toDateString(),
                'is_active' => true,
            ],
        ];

        foreach ($announcementData as $ann) {
            Announcement::firstOrCreate(
                ['title' => $ann['title']],
                array_merge($ann, ['created_by' => $adminId])
            );
        }

        // Create claims for some employees
        $claimCategories = ['Transport', 'Meal', 'Accommodation', 'Office Supplies', 'Medical', 'Training'];

        foreach ($allEmployees->take(4) as $index => $emp) {
            $claimStatus = ['Draft', 'Pending', 'Approved', 'Paid'][$index % 4];

            $claim = Claim::firstOrCreate(
                ['user_id' => $emp->id, 'title' => "Expense Claim - " . Carbon::now()->format('M Y')],
                [
                    'user_id' => $emp->id,
                    'title' => "Expense Claim - " . Carbon::now()->format('M Y'),
                    'description' => 'Monthly expense reimbursement claim',
                    'total_amount' => 0,
                    'status' => $claimStatus,
                    'approved_by' => in_array($claimStatus, ['Approved', 'Paid']) ? $adminId : null,
                    'approved_at' => in_array($claimStatus, ['Approved', 'Paid']) ? now() : null,
                ]
            );

            // Add 2-3 items per claim
            $itemCount = rand(2, 3);
            for ($j = 0; $j < $itemCount; $j++) {
                $amount = rand(15, 200) + (rand(0, 99) / 100);
                ClaimItem::firstOrCreate(
                    ['claim_id' => $claim->id, 'description' => fake()->sentence(3)],
                    [
                        'claim_id' => $claim->id,
                        'description' => fake()->sentence(3),
                        'amount' => round($amount, 2),
                        'expense_date' => now()->subDays(rand(1, 20))->toDateString(),
                        'category' => $claimCategories[array_rand($claimCategories)],
                        'notes' => rand(0, 1) ? fake()->sentence() : null,
                    ]
                );
            }

            $claim->recalculateTotal();
        }

        // Create calendar events
        $eventTypes = ['Personal', 'Meeting', 'Deadline', 'Reminder'];

        foreach ($allEmployees->take(5) as $emp) {
            for ($j = 0; $j < rand(1, 3); $j++) {
                $eventDate = now()->addDays(rand(1, 60));
                CalendarEvent::firstOrCreate(
                    ['user_id' => $emp->id, 'title' => fake()->sentence(3), 'event_date' => $eventDate->toDateString()],
                    [
                        'user_id' => $emp->id,
                        'title' => fake()->sentence(3),
                        'description' => fake()->sentence(),
                        'event_date' => $eventDate->toDateString(),
                        'event_time' => sprintf('%02d:%02d', rand(9, 17), rand(0, 59)),
                        'type' => $eventTypes[array_rand($eventTypes)],
                        'notify_supervisor' => rand(0, 1) ? true : false,
                        'reminder_sent' => false,
                    ]
                );
            }
        }

        // Seed messages between employees and supervisors
        $admin = User::where('email', 'admin@example.com')->first();
        $supervisor = User::where('email', 'supervisor@example.com')->first();
        $firstEmployee = $allEmployees->first();

        if ($admin && $supervisor && $firstEmployee) {
            // Employee → Supervisor message thread
            $msg1 = Message::firstOrCreate(
                ['sender_id' => $firstEmployee->id, 'receiver_id' => $supervisor->id, 'subject' => 'Leave Request Inquiry'],
                [
                    'sender_id' => $firstEmployee->id,
                    'receiver_id' => $supervisor->id,
                    'subject' => 'Leave Request Inquiry',
                    'body' => 'Hi, I would like to inquire about taking leave next week for a family event. Is it possible to take 2 days off?',
                    'is_read' => true,
                    'read_at' => now()->subDays(2),
                    'created_at' => now()->subDays(3),
                    'updated_at' => now()->subDays(2),
                ]
            );

            // Supervisor reply
            Message::firstOrCreate(
                ['sender_id' => $supervisor->id, 'receiver_id' => $firstEmployee->id, 'parent_id' => $msg1->id, 'subject' => 'Leave Request Inquiry'],
                [
                    'sender_id' => $supervisor->id,
                    'receiver_id' => $firstEmployee->id,
                    'subject' => 'Leave Request Inquiry',
                    'body' => 'Sure, please submit the leave application through the system and I will approve it.',
                    'parent_id' => $msg1->id,
                    'is_read' => true,
                    'read_at' => now()->subDays(1),
                    'created_at' => now()->subDays(2),
                    'updated_at' => now()->subDays(1),
                ]
            );

            // Supervisor → Admin message
            $msg2 = Message::firstOrCreate(
                ['sender_id' => $supervisor->id, 'receiver_id' => $admin->id, 'subject' => 'Team Attendance Report'],
                [
                    'sender_id' => $supervisor->id,
                    'receiver_id' => $admin->id,
                    'subject' => 'Team Attendance Report',
                    'body' => 'Hi Admin, I noticed some inconsistencies in the attendance records for my team this month. Could you please review the data?',
                    'is_read' => false,
                    'created_at' => now()->subDay(),
                    'updated_at' => now()->subDay(),
                ]
            );

            // Employee → Supervisor (unread)
            Message::firstOrCreate(
                ['sender_id' => $firstEmployee->id, 'receiver_id' => $supervisor->id, 'subject' => 'WFH Request'],
                [
                    'sender_id' => $firstEmployee->id,
                    'receiver_id' => $supervisor->id,
                    'subject' => 'WFH Request',
                    'body' => 'Good morning, may I work from home tomorrow? I have a plumber coming to fix some pipes at my house.',
                    'is_read' => false,
                    'created_at' => now()->subHours(3),
                    'updated_at' => now()->subHours(3),
                ]
            );
        }

        // Assign HOD to IT department
        $supervisor = User::where('email', 'supervisor@example.com')->first();
        if ($supervisor) {
            \App\Models\Department::where('id', $itDept)->update(['hod_id' => $supervisor->id]);
        }

        // Seed holidays if not already done
        $this->call(HolidaySeeder::class);
    }
}
