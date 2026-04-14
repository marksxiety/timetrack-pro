<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->index(['user_id', 'date'], 'schedules_user_id_date_index');
        });

        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->index('updated_at', 'overtime_requests_updated_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex('schedules_user_id_date_index');
        });

        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->dropIndex('overtime_requests_updated_at_index');
        });
    }
};
