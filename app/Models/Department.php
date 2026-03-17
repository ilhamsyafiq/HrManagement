<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name', 'description', 'hod_id'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function hod()
    {
        return $this->belongsTo(User::class, 'hod_id');
    }
}
