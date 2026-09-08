<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->unsignedTinyInteger('current_approval_level')->default(1)->after('status');
            $table->boolean('requires_dean_approval')->default(true)->after('current_approval_level');
            $table->foreignId('dean_id')->nullable()->constrained('users')->nullOnDelete()->after('requires_dean_approval');
            $table->foreignId('hr_id')->nullable()->constrained('users')->nullOnDelete()->after('dean_id');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['hr_id']);
            $table->dropColumn('hr_id');
            $table->dropForeign(['dean_id']);
            $table->dropColumn('dean_id');
            $table->dropColumn('requires_dean_approval');
            $table->dropColumn('current_approval_level');
        });
    }
};
