<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('required_hours', function (Blueprint $table) {
            $table->foreignId('organization_unit_id')
                ->constrained()->nullable()->after('week')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('required_hours', function (Blueprint $table) {
            $table->dropForeign(['organization_unit_id']);
            $table->dropColumn('organization_unit_id');
        });
    }
};
