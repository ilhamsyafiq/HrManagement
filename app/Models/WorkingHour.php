<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingHour extends Model
{
    protected $fillable = [
        'user_id', 'work_start', 'work_end', 'break_start', 'break_end',
        'late_threshold_minutes', 'early_leave_threshold_minutes', 'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getForUser($userId)
    {
        return static::where('user_id', $userId)->first()
            ?? static::where('is_default', true)->first();
    }
}
