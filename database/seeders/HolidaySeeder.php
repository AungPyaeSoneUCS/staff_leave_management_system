<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $defaults = [
            ['name' => 'Saturday', 'date' => null, 'is_recurring' => true, 'is_default' => true, 'description' => 'Default weekly holiday'],
            ['name' => 'Sunday', 'date' => null, 'is_recurring' => true, 'is_default' => true, 'description' => 'Default weekly holiday'],
        ];

        foreach ($defaults as $holiday) {
            Holiday::firstOrCreate(
                ['name' => $holiday['name'], 'is_default' => true],
                $holiday
            );
        }
    }
}
