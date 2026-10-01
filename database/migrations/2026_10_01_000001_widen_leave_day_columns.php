<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->decimal('total_days', 8, 2)->change();
        });

        Schema::table('leave_balances', function (Blueprint $table) {
            $table->decimal('allocated_days', 8, 2)->change();
            $table->decimal('used_days', 8, 2)->default(0)->change();
            $table->decimal('remaining_days', 8, 2)->change();
        });

        DB::statement('UPDATE leave_balances SET used_days = GREATEST(used_days, 0)');
        DB::statement('UPDATE leave_balances SET remaining_days = GREATEST(allocated_days - used_days, 0)');
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->decimal('total_days', 5, 2)->change();
        });

        Schema::table('leave_balances', function (Blueprint $table) {
            $table->decimal('allocated_days', 5, 2)->change();
            $table->decimal('used_days', 5, 2)->default(0)->change();
            $table->decimal('remaining_days', 5, 2)->change();
        });
    }
};
