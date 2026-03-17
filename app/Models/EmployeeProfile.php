<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeProfile extends Model
{
    protected $fillable = [
        'user_id', 'phone', 'ic_number', 'date_of_birth', 'gender', 'marital_status',
        'address', 'city', 'state', 'postcode', 'country',
        'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship',
        'bank_name', 'bank_account_number', 'epf_number', 'socso_number', 'tax_number',
        'job_title', 'hire_date', 'basic_salary', 'profile_photo',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'hire_date' => 'date',
            'basic_salary' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
