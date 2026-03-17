<?php

namespace App\Policies;

use App\Models\Leave;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LeavePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Leave $leave): bool
    {
        return $leave->user_id === $user->id || $user->isSuperAdmin() || $user->isAdmin() || 
               ($user->isSupervisor() && $leave->user->supervisor_id === $user->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Leave $leave): bool
    {
        return $leave->user_id === $user->id && $leave->status === 'Pending';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Leave $leave): bool
    {
        return $leave->user_id === $user->id && $leave->status === 'Pending';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Leave $leave): bool
    {
        return $leave->user_id === $user->id && $leave->status === 'Pending';
    }

    public function approve(User $user, Leave $leave): bool
    {
        // Supervisor/HOD can approve Pending leaves from their subordinates
        if ($user->isSupervisor() && $leave->status === 'Pending'
            && $leave->user->supervisor_id === $user->id) {
            // For interns: first-tier approval (Supervisor Approved)
            // For employees: direct approval
            return true;
        }

        // Admin/Super Admin can approve:
        // - Pending leaves (direct approval)
        // - Supervisor Approved intern leaves (second-tier)
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            if ($leave->status === 'Pending') {
                return true;
            }
            if ($leave->status === 'Supervisor Approved') {
                return true;
            }
        }

        return false;
    }
    public function forceDelete(User $user, Leave $leave): bool
    {
        return false;
    }
}
