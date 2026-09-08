<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->boolean('track_on_use')->default(false)->after('is_not_limited');
        });
    }

    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn('track_on_use');
        });
    }
};