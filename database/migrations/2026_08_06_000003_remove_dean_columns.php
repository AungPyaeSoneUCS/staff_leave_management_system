<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['dean_id']);
            $table->dropColumn('dean_id');
            $table->dropColumn('requires_dean_approval');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['dean_id']);
            $table->dropColumn('dean_id');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->foreignId('dean_id')->nullable()->constrained('users')->nullOnDelete()->after('head_id');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->boolean('requires_dean_approval')->default(true)->after('current_approval_level');
            $table->foreignId('dean_id')->nullable()->constrained('users')->nullOnDelete()->after('requires_dean_approval');
        });
    }
};
