<?php

namespace Tests\Feature;

use App\Http\Controllers\PayrollController;
use App\Models\Payroll;
use App\Models\User;
use App\Services\LeaveBalanceService;
use Tests\TestCase;

class BulkFeaturesTest extends TestCase
{
    private function role(string $r): ?User
    {
        return User::whereHas('role', fn ($q) => $q->where('name', $r))->first();
    }

    public function test_bulk_features(): void
    {
        $admin = $this->role('Super Admin');
        $emp = $this->role('Employee');
        $this->assertNotNull($admin);
        $this->assertNotNull($emp);
        $out = [];

        // New Filament resources render for admin
        foreach (['/panel/shifts', '/panel/shift-assignments', '/panel/documents', '/panel/payrolls'] as $u) {
            $code = $this->actingAs($admin)->get($u)->getStatusCode();
            $out[] = "ADM $u => $code";
            $this->assertEquals(200, $code, "$u failed");
        }

        // Leave balance service works
        $bal = LeaveBalanceService::for($emp);
        $out[] = 'LeaveBalance AL => ' . ($bal['AL']['remaining'] ?? 'n/a') . '/' . ($bal['AL']['entitlement'] ?? 'n/a');
        $this->assertArrayHasKey('AL', $bal);

        // Payroll generate for one employee + payslip PDF
        $month = '2026-06';
        Payroll::where('user_id', $emp->id)->where('month', $month)->delete(); // clean slate
        PayrollController::generatePayrollFor([$emp], $month, $admin->id);
        $payroll = Payroll::where('user_id', $emp->id)->where('month', $month)->first();
        $out[] = 'Payroll generated => net RM' . ($payroll->net_salary ?? 'NULL') . ', EPF ee ' . ($payroll->epf_employee ?? 'NULL');
        $this->assertNotNull($payroll, 'payroll not generated');

        $pdf = $this->actingAs($emp)->get(route('payroll.payslip.pdf', $payroll));
        $out[] = 'Payslip PDF => ' . $pdf->getStatusCode() . ' ' . $pdf->headers->get('content-type');
        $pdf->assertStatus(200);

        // Employee payroll listing (nav target)
        $out[] = 'EMP /payroll => ' . $this->actingAs($emp)->get('/payroll')->getStatusCode();

        // cleanup
        Payroll::where('user_id', $emp->id)->where('month', $month)->delete();

        fwrite(STDERR, "\n" . implode("\n", $out) . "\n");
    }
}
