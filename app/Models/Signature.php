<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A user's ONE reusable default signature (the supervisor's stored
 * signature/"chop"). Queried directly via Signature::where('user_id', ...);
 * no relation is added to the User model on purpose.
 */
class Signature extends Model
{
    protected $fillable = [
        'user_id',
        'signature_data',
    ];
}
