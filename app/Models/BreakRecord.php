<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BreakRecord extends Model
{
    protected $table = 'breaks';

    protected $fillable = [
        'attendance_id', 'break_in', 'break_in_lat', 'break_in_lng', 'break_in_address',
        'break_out', 'break_out_lat', 'break_out_lng', 'break_out_address',
        'duration_minutes', 'is_manually_edited', 'edited_by', 'edit_reason', 'edited_at'
    ];

    protected function casts(): array
    {
        return [
            'break_in' => 'datetime',
            'break_out' => 'datetime',
            'edited_at' => 'datetime',
        ];
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }
}
