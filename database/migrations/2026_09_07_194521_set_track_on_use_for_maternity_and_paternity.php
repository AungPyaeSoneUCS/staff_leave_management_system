<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('leave_types')
            ->whereIn('code', ['MATERNITY', 'MML', 'PATERNITY', 'PL'])
            ->update(['track_on_use' => true]);
    }

    public function down(): void
    {
        DB::table('leave_types')
            ->whereIn('code', ['MATERNITY', 'MML', 'PATERNITY', 'PL'])
            ->update(['track_on_use' => false]);
    }
};