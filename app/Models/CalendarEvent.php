<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $fillable = [
        'user_id', 'title', 'description', 'event_date', 'event_time',
        'type', 'notify_supervisor', 'reminder_sent',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'notify_supervisor' => 'boolean',
            'reminder_sent' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', now()->toDateString())->orderBy('event_date');
    }

    public function scopeNeedsReminder($query)
    {
        return $query->where('reminder_sent', false)
            ->where('event_date', now()->addDay()->toDateString());
    }
}
