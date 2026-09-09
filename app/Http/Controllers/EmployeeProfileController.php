<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EmployeeDocument;
use App\Models\EmployeeProfile;
use App\Models\EmploymentHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeProfileController extends Controller
{
    public function show($id = null)
    {
        $user = auth()->user();
        $isAdmin = $user->isSuperAdmin() || $user->isAdmin();

        if ($id && $isAdmin) {
            $employee = User::with(['profile', 'employeeDocuments', 'employmentHistories', 'department'])->findOrFail($id);
        } else {
            $employee = User::with(['profile', 'employeeDocuments', 'employmentHistories', 'department'])->findOrFail($user->id);
        }

        return view('employee-profile.show', compact('employee', 'isAdmin'));
    }

    public function edit($id = null)
    {
        $user = auth()->user();
        $isAdmin = $user->isSuperAdmin() || $user->isAdmin();

        if ($id && $isAdmin) {
            $employee = User::with('profile')->findOrFail($id);
        } else {
            $employee = User::with('profile')->findOrFail($user->id);
        }

        return view('employee-profile.edit', compact('employee', 'isAdmin'));
    }

    public function update(Request $request, $id = null)
    {
        $user = auth()->user();
        $isAdmin = $user->isSuperAdmin() || $user->isAdmin();

        if ($id && $isAdmin) {
            $employee = User::findOrFail($id);
        } else {
            $employee = $user;
        }

        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'ic_number' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other',
            'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'epf_number' => 'nullable|string|max:50',
            'socso_number' => 'nullable|string|max:50',
            'tax_number' => 'nullable|string|max:50',
            'job_title' => 'nullable|string|max:255',
            'hire_date' => 'nullable|date',
            'basic_salary' => 'nullable|numeric|min:0',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Employer-owned fields must never be settable via employee self-service
        // (prevents an employee inflating their own salary / job title / hire date,
        // which feed payroll). Only an Admin / Super Admin may set these.
        if (!$isAdmin) {
            unset($validated['basic_salary'], $validated['job_title'], $validated['hire_date']);
        }

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $validated['profile_photo'] = $path;

            if ($employee->profile && $employee->profile->profile_photo) {
                Storage::disk('public')->delete($employee->profile->profile_photo);
            }
        }

        $employee->profile()->updateOrCreate(
            ['user_id' => $employee->id],
            $validated
        );

        $redirectRoute = $isAdmin && $id
            ? route('employee-profile.show', $id)
            : route('employee-profile.show');

        return redirect($redirectRoute)->with('success', 'Profile updated successfully.');
    }

    public function storeDocument(Request $request, $userId)
    {
        $user = auth()->user();
        $isAdmin = $user->isSuperAdmin() || $user->isAdmin();

        if (!$isAdmin && $user->id != $userId) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:Contract,Certificate,ID,Resume,Other',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $file = $request->file('file');
        // Private (local) disk: ID / contract documents must not be public.
        $path = $file->store('employee-documents');

        EmployeeDocument::create([
            'user_id' => $userId,
            'title' => $request->title,
            'category' => $request->category,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'expiry_date' => $request->expiry_date,
            'notes' => $request->notes,
            'uploaded_by' => $user->id,
        ]);

        return redirect()->route('employee-profile.show', $userId)->with('success', 'Document uploaded successfully.');
    }

    public function downloadDocument(EmployeeDocument $document)
    {
        $user = auth()->user();
        $isAdmin = $user->isSuperAdmin() || $user->isAdmin();

        if (!$isAdmin && $user->id != $document->user_id) {
            abort(403);
        }

        abort_unless(Storage::exists($document->file_path), 404);

        return response()->download(Storage::path($document->file_path), $document->file_name);
    }

    public function deleteDocument(EmployeeDocument $document)
    {
        $user = auth()->user();
        $isAdmin = $user->isSuperAdmin() || $user->isAdmin();

        if (!$isAdmin && $user->id != $document->user_id) {
            abort(403);
        }

        Storage::delete($document->file_path);
        $document->delete();

        return redirect()->back()->with('success', 'Document deleted successfully.');
    }

    public function storeHistory(Request $request, $userId)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'action' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'salary' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:500',
            'effective_date' => 'required|date',
        ]);

        EmploymentHistory::create([
            'user_id' => $userId,
            'action' => $request->action,
            'position' => $request->position,
            'department' => $request->department,
            'salary' => $request->salary,
            'remarks' => $request->remarks,
            'effective_date' => $request->effective_date,
            'performed_by' => $user->id,
        ]);

        return redirect()->route('employee-profile.show', $userId)->with('success', 'Employment history record added.');
    }
}
