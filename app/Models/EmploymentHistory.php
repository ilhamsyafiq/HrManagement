<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmploymentHistory extends Model
{
    protected $fillable = [
        'user_id', 'action', 'position', 'department', 'salary',
        'remarks', 'effective_date', 'performed_by',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'salary' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
