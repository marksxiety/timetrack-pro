<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remove duplicate schedules so each user has at most one schedule per day.
     * For every group of duplicates sharing (user_id, date), the row with the
     * latest updated_at is retained (id is used as a tie-breaker).
     */
    public function up(): void
    {
        DB::transaction(function () {
            $duplicates = DB::table('schedules')
                ->select('user_id', 'date')
                ->groupBy('user_id', 'date')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            foreach ($duplicates as $group) {
                $rows = DB::table('schedules')
                    ->where('user_id', $group->user_id)
                    ->where('date', $group->date)
                    ->orderByDesc('updated_at')
                    ->orderByDesc('id')
                    ->get();

                $keep = $rows->shift();

                foreach ($rows as $row) {
                    DB::table('overtime_requests')
                        ->where('employee_schedule_id', $row->id)
                        ->update(['employee_schedule_id' => $keep->id]);

                    DB::table('schedules')->where('id', $row->id)->delete();
                }
            }
        });
    }

    public function down(): void
    {
        // Irreversible: deleted duplicates cannot be restored.
    }
};
