<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE overtime_requests MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'PENDING'");
            DB::statement("ALTER TABLE overtime_requests MODIFY COLUMN remarks VARCHAR(191) NULL");
        } else {
            Schema::table('overtime_requests', function (Blueprint $table) {
                $table->string('status', 50)->default('PENDING')->change();
                $table->string('remarks', 191)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE overtime_requests MODIFY COLUMN status TEXT NOT NULL DEFAULT 'PENDING'");
            DB::statement("ALTER TABLE overtime_requests MODIFY COLUMN remarks TEXT NULL");
        } else {
            Schema::table('overtime_requests', function (Blueprint $table) {
                $table->text('status')->default('PENDING')->change();
                $table->text('remarks')->nullable()->change();
            });
        }
    }
};
