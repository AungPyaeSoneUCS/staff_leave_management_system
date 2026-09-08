<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'admin', 'department_head', 'staff'])
                ->default('staff')
                ->change();
            $table->unsignedInteger('position_level')->nullable();
        });

        Schema::create('admin_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('leave_request_id')->constrained()->cascadeOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->unique(['admin_id', 'leave_request_id']);
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->foreignId('duty_exchange_user_id')->nullable()->after('reason')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['duty_exchange_user_id']);
            $table->dropColumn('duty_exchange_user_id');
        });

        Schema::dropIfExists('admin_assignments');

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'department_head', 'staff'])
                ->default('staff')
                ->change();
            $table->dropColumn('position_level');
        });
    }
};
