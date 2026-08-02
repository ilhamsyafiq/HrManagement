<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A single non-contiguous work block within a split shift.
 *
 * Times are stored as raw "HH:MM[:SS]" strings (no datetime cast) so consumers
 * can format/parse them however they need. Rows are ordered by sort_order.
 */
class ShiftSegment extends Model
{
    protected $fillable = [
        'shift_id',
        'sort_order',
        'start_time',
        'end_time',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('ordered', function ($query) {
            $query->orderBy('sort_order');
        });
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
