<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->foreignId('cancelled_by_id')->nullable()->constrained('users')->nullOnDelete()->after('hr_id');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by_id');
            $table->text('cancellation_reason')->nullable()->after('cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by_id']);
            $table->dropColumn(['cancelled_by_id', 'cancelled_at', 'cancellation_reason']);
        });
    }
};
