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
            $table->text('reviewer_remarks')->nullable()->after('review_remarks');
            $table->text('hr_remarks')->nullable()->after('reviewer_remarks');
            $table->text('super_admin_remarks')->nullable()->after('hr_remarks');
        });

        DB::table('leave_requests')
            ->whereNotNull('review_remarks')
            ->whereNotNull('reviewer_id')
            ->update(['reviewer_remarks' => DB::raw('review_remarks')]);

        DB::table('leave_requests')
            ->whereNotNull('review_remarks')
            ->whereNull('reviewer_id')
            ->whereNotNull('hr_id')
            ->update(['hr_remarks' => DB::raw('review_remarks')]);

        DB::table('leave_requests')
            ->whereNotNull('review_remarks')
            ->whereNull('reviewer_id')
            ->whereNull('hr_id')
            ->whereNotNull('super_admin_id')
            ->update(['super_admin_remarks' => DB::raw('review_remarks')]);
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn(['reviewer_remarks', 'hr_remarks', 'super_admin_remarks']);
        });
    }
};