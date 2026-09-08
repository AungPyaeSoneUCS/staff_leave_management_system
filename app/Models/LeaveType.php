<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = [
        'name',
        'name_mm',
        'code',
        'description',
        'description_mm',
        'annual_allocation',
        'per_leave_days',
        'carry_forward_limit',
        'requires_attachment',
        'is_not_limited',
        'track_on_use',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'annual_allocation' => 'integer',
            'carry_forward_limit' => 'integer',
            'requires_attachment' => 'boolean',
            'is_not_limited' => 'boolean',
            'track_on_use' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
