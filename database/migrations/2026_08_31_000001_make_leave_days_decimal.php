<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->integer('total_days')->change();
        });

        Schema::table('leave_balances', function (Blueprint $table) {
            $table->integer('allocated_days')->change();
            $table->integer('used_days')->default(0)->change();
            $table->integer('remaining_days')->change();
        });
    }
};
