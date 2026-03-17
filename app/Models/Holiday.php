<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'name', 'date', 'type', 'description', 'is_recurring', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_recurring' => 'boolean',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now('Asia/Kuala_Lumpur')->toDateString())
                     ->orderBy('date');
    }

    public function scopeInYear($query, $year)
    {
        return $query->whereYear('date', $year)->orderBy('date');
    }
}
