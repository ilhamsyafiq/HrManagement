<?php

namespace App\Services;

use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Who a given user is allowed to message / chat with. Single source of truth
 * shared by the legacy Messages page and the chat widget.
 *
 * Rules:
 *  - Admin / Super Admin: anyone (except self).
 *  - Supervisor: their subordinates + Admins (HR).
 *  - Employee / Intern: direct supervisor, department HOD, Admins (HR),
 *    same-supervisor teammates, and same-department members.
 *  - Super Admin is NOT messageable by regular users (only Admins can reach them).
 */
class RecipientResolver
{
    public static function allowedFor(User $user): Collection
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return User::where('id', '!=', $user->id)->orderBy('name')->get();
        }

        if ($user->isSupervisor()) {
            $subordinateIds = $user->subordinates()->pluck('id');
            $adminIds = User::whereHas('role', fn ($q) => $q->whereIn('name', ['Admin']))->pluck('id');

            return User::whereIn('id', $subordinateIds->merge($adminIds)->unique())
                ->where('id', '!=', $user->id)
                ->orderBy('name')
                ->get();
        }

        // Employees / Interns
        $recipientIds = collect();

        if ($user->supervisor_id) {
            $recipientIds->push($user->supervisor_id);
        }
        if ($user->department && $user->department->hod_id) {
            $recipientIds->push($user->department->hod_id);
        }

        $recipientIds = $recipientIds->merge(
            User::whereHas('role', fn ($q) => $q->whereIn('name', ['Admin']))->pluck('id')
        );

        if ($user->supervisor_id) {
            $recipientIds = $recipientIds->merge(
                User::where('supervisor_id', $user->supervisor_id)->where('id', '!=', $user->id)->pluck('id')
            );
        }
        if ($user->department_id) {
            $recipientIds = $recipientIds->merge(
                User::where('department_id', $user->department_id)->where('id', '!=', $user->id)->pluck('id')
            );
        }

        $recipientIds = $recipientIds->filter()->unique()->reject(fn ($id) => $id === $user->id);

        return User::whereIn('id', $recipientIds)
            ->whereDoesntHave('role', fn ($q) => $q->where('name', 'Super Admin'))
            ->orderBy('name')
            ->get();
    }
}
