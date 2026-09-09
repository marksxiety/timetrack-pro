<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasIndex('schedules', 'schedules_user_id_date_index')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->dropIndex('schedules_user_id_date_index');
            });
        }

        if (! Schema::hasIndex('schedules', 'schedules_user_id_date_unique')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->unique(['user_id', 'date'], 'schedules_user_id_date_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('schedules', 'schedules_user_id_date_unique')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->dropUnique('schedules_user_id_date_unique');
            });
        }

        if (! Schema::hasIndex('schedules', 'schedules_user_id_date_index')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->index(['user_id', 'date'], 'schedules_user_id_date_index');
            });
        }
    }
};
