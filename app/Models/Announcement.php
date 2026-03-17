<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title', 'content', 'priority', 'target', 'department_id',
        'target_role', 'publish_date', 'expiry_date', 'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'publish_date' => 'date',
            'expiry_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('publish_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now());
            });
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('target', 'All')
                ->orWhere(function ($q2) use ($user) {
                    $q2->where('target', 'Department')->where('department_id', $user->department_id);
                })
                ->orWhere(function ($q2) use ($user) {
                    $q2->where('target', 'Role')->where('target_role', $user->role->name);
                });
        });
    }
}
