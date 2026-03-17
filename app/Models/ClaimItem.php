<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClaimItem extends Model
{
    protected $fillable = [
        'claim_id', 'description', 'amount', 'expense_date', 'category',
        'receipt_path', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
        ];
    }

    public function claim()
    {
        return $this->belongsTo(Claim::class);
    }
}
