<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('admin_assignments');

        Schema::create('admin_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('leave_request_id')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->unique('admin_id');
            $table->foreign('leave_request_id')->nullable()->references('id')->on('leave_requests')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_assignments');

        Schema::create('admin_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('leave_request_id')->constrained()->cascadeOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->unique(['admin_id', 'leave_request_id']);
        });
    }
};
