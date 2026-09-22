<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByRaw('sort_order = 0')
            ->orderBy('sort_order')
            ->orderBy('name');
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
