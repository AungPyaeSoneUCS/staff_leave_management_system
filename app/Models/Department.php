<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Department extends Model
{
    protected $fillable = [
        'name',
        'name_mm',
        'code',
        'description',
        'head_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_id');
    }

    public function headOf(): HasMany
    {
        return $this->hasMany(Department::class, 'head_id');
    }

    public function departmentHeadOf(): HasOne
    {
        return $this->hasOne(Department::class, 'head_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
