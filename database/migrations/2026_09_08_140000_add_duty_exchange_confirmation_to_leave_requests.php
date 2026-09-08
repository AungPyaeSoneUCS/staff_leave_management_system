<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('duty_exchange_status')->nullable()->after('duty_exchange_user_id');
            $table->timestamp('duty_exchange_confirmed_at')->nullable()->after('duty_exchange_status');
            $table->text('duty_exchange_remarks')->nullable()->after('duty_exchange_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn(['duty_exchange_status', 'duty_exchange_confirmed_at', 'duty_exchange_remarks']);
        });
    }
};