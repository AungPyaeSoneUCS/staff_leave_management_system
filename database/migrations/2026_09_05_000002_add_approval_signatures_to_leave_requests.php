<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->text('staff_signature')->nullable()->after('reason');
            $table->text('hr_signature')->nullable()->after('reviewer_signature');
            $table->text('super_admin_signature')->nullable()->after('hr_signature');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn(['staff_signature', 'hr_signature', 'super_admin_signature']);
        });
    }
};