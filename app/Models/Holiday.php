<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'name',
        'name_mm',
        'date',
        'is_recurring',
        'is_default',
        'description',
        'description_mm',
        'replaced_holiday_id',
        'replacement_note',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_recurring' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function replacedHoliday()
    {
        return $this->belongsTo(Holiday::class, 'replaced_holiday_id');
    }

    public function replacements()
    {
        return $this->hasMany(Holiday::class, 'replaced_holiday_id');
    }
}
