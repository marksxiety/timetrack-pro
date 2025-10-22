<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\OvertimeRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Schedule;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isEmpty;

class ScheduleController extends Controller
{
    public function fetchSchedule(Request $request)
    {
        try {
            $days = [];
            $year = $request->input('year', now()->year);
            $week = $request->input('week', now()->week);

            $date = CarbonImmutable::createFromDate($year, 1, 1, 'Asia/Manila')->startOfWeek(Carbon::SUNDAY)->addWeeks($week - 1);

            // Loop through 7 days of the week (data displaying is by week only)
            for ($i = 0; $i < 7; $i++) {
                $current = $date->addDays($i);
                $days[] = [
                    'id' => null,
                    'date' => $current->toDateString(),
                    'week' => $week,
                    'day' => $current->format('l'),
                    'shift_code' => null
                ];
            }

            $schedules = Schedule::where('user_id',  Auth::id())->whereYear('date', $year)->where('week', $week)->get();

            // populate the shift_code value if it matches the date
            // this will identify if the user has a current schedule on the specific day
            foreach ($schedules as &$schedule) {
                for ($j = 0; $j < count($days); $j++) {
                    if ($schedule['date'] === $days[$j]['date']) {
                        $days[$j]['id'] = $schedule['id'];
                        $days[$j]['shift_code'] = $schedule['shift_id'];
                        break;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'proceed',
                'schedules' => $days,
                'id' =>  Auth::id()
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'schedules' => [],
                'success' => false,
                'message' => "Failed to fetch schedules due to $th"
            ]);
        }
    }

    public function submitSchedule(Request $request)
    {
        $info = $request->input('schedule');
        $resultSchedules = [];

        try {
            foreach ($info as $item) {

                if (!empty($item['id'])) {
                    Schedule::where('id', $item['id'])->update([
                        'user_id' => Auth::id(),
                        'shift_id' => $item['shift_code'],
                        'date' => $item['date'],
                        'week' => $item['week'],
                    ]);

                    $updated = Schedule::find($item['id']);
                    $resultSchedules[] = [
                        'id' => $updated->id,
                        'date' => $updated->date,
                        'week' => $updated->week,
                        'day' => $item['day'],
                        'shift_code' => $updated->shift_id,
                    ];
                } else {
                    if (empty($item['shift_code'])) {
                        $resultSchedules[] = [
                            'id' => null,
                            'date' => $item['date'],
                            'week' => $item['week'],
                            'day' => $item['day'],
                            'shift_code' => null
                        ];
                        continue;
                    }

                    $created = Schedule::create([
                        'user_id' => Auth::id(),
                        'shift_id' => $item['shift_code'],
                        'date' => $item['date'],
                        'week' => $item['week'],
                    ]);

                    $resultSchedules[] = [
                        'id' => $created->id,
                        'date' => $created->date,
                        'week' => $created->week,
                        'day' => $item['day'],
                        'shift_code' => $created->shift_id,
                    ];
                }
            }

            $success = true;
            $message = 'Submission Successful';
        } catch (\Throwable $th) {
            $success = false;
            $message = 'Submission Failed';
            $resultSchedules = [];
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'schedules' => $resultSchedules
        ]);
    }


    public function getUserSchedule(Request $request)
    {
        $registered = [];
        try {
            $schedule = Schedule::with('shift')->where('user_id', Auth::id())->whereYear('date', $request->year)->whereMonth('date', $request->month)->whereDay('date', $request->day)->get(
                ['id', 'shift_id', 'date', 'week']
            )->first();

            if ($schedule) {
                $registered = [
                    'id' => $schedule->id,
                    'shift_id' => $schedule->shift_id,
                    'shift_code' => $schedule->shift?->code,
                    'shift_start_time' => ($schedule->shift->start_time == null) ? '--' : date('h:i A', strtotime($schedule->shift?->start_time)),
                    'shift_end_time' => ($schedule->shift->end_time == null) ? '--' : date('h:i A', strtotime($schedule->shift?->end_time)),
                    'date' => $schedule->date,
                    'week' => $schedule->week
                ];
            }

            $success = true;
            $message = 'Fetching Successful';
        } catch (\Throwable $th) {
            $success = false;
            $message = 'Fetching Failed ' . $th;
            $schedule = null;
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'schedule' => $registered
        ]);
    }

    public function fetchEmployeeSchedule(Request $request)
    {
        $schedules = [];
        $employees = [];

        try {
            $days = [];

            // Get year and week from request, default to current year/week if not provided
            $year = $request->input('year', now()->year);
            $week = $request->input('week', now()->week);

            // Get the first day (Sunday) of the requested week in the given year
            $date = CarbonImmutable::createFromDate($year, 1, 1, 'Asia/Manila')
                ->startOfWeek(Carbon::SUNDAY)
                ->addWeeks($week - 1);

            // Fetch all registered employees (base dataset for generating schedules)
            $employees = User::where([
                ['role', 'employee'],
                ['organization_unit_id', Auth::user()->organization_unit_id],
            ])->get();

            // Prepare an array for all 7 days of the selected week
            for ($i = 0; $i < 7; $i++) {
                $current = $date->addDays($i);
                $days[] = [
                    'date' => $current->toDateString(),
                    'week' => $week,
                    'day' => $current->format('l'), // day name (e.g., Monday)
                ];
            }

            // get the first and last date of the week
            $week_start = Carbon::parse($days[0]['date'])->toFormattedDateString();
            $week_end = Carbon::parse($days[(count($days) - 1)]['date'])->toFormattedDateString();

            // Build a base schedule template for each employee across all 7 days
            $employee_schedules = [];
            foreach ($employees as $employee) {
                foreach ($days as $day) {

                    // find the index where the employee id is existing
                    $index = array_search($employee->id, array_column($employee_schedules, 'user_id'));

                    // if the index return the exact index of the value
                    // push to schedule array of the user, this will merge all the schedule of specific user in an index
                    if ($index !== false) {
                        $employee_schedules[$index]['schedule'][] = [
                            'shift_id' => null,
                            'schedule_id' => null,
                            'date' => $day['date'],
                            'week' => $day['week'],
                            'day' => $day['day']
                        ];
                    } else {
                        $employee_schedules[] = [
                            'user_id' => $employee->id,
                            'name' => $employee->name,
                            'schedule' => [[
                                'shift_id' => null,
                                'schedule_id' => null,
                                'date' => $day['date'],
                                'week' => $day['week'],
                                'day' => $day['day']
                            ]]
                        ];
                    }
                }
            }

            // Fetch actual schedules from DB for the given week/year
            $schedules = User::leftJoin('schedules', 'users.id', '=', 'schedules.user_id')
                ->where('users.role', 'employee')
                ->whereYear('schedules.date', $year)
                ->where('schedules.week', $week)
                ->select(
                    'users.id as user_id',
                    'users.name',
                    'schedules.id as schedule_id',
                    'schedules.shift_id',
                    'schedules.date',
                    'schedules.week',
                )
                ->get();

            // Match actual schedules with the base schedule template
            // If a match is found (same date and user), update shift_id and schedule_id
            foreach ($schedules as &$schedule) {
                for ($j = 0; $j < count($employee_schedules); $j++) {
                    if ($schedule['user_id'] === $employee_schedules[$j]['user_id']) {
                        for ($s = 0; $s < count($employee_schedules[$j]['schedule']); $s++) {
                            if ($employee_schedules[$j]['schedule'][$s]['date'] == $schedule['date']) {
                                $employee_schedules[$j]['schedule'][$s]['schedule_id'] = $schedule['schedule_id'];
                                $employee_schedules[$j]['schedule'][$s]['shift_id'] = $schedule['shift_id'];
                                break;
                            }
                        }
                    }
                }
            }

            $success = true;
            $message = "Employee's Schedule loaded successfully";
        } catch (\Throwable $th) {
            $schedules = [];
            $success = false;
            $message = "Failed to fetch schedules due to $th";
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'info' => [
                'schedules' => $employee_schedules, // Final merged employee schedule data
                'week_start' => $week_start,
                'week_end' => $week_end,
                'week' => $week,
                'year' => $year
            ]
        ]);
    }

    public function submitEmployeeSchedules(Request $request)
    {
        $req = $request->input('schedule', []);
        $message = '';
        $success = false;

        try {
            if (count($req) === 0) {
                return response()->json([
                    'success' => $success,
                    'message' => 'invalid request'
                ]);
            }

            foreach ($req as $r) {
                // r contains week, year, week_schedule (array[])
                // iterate the week_schedule
                foreach ($r['week_schedule'] as $sched) {
                    // iteral the schedule to identify if the id is null
                    // if null, insert to db else update it.

                    for ($i = 0; $i < count($sched['schedule']); $i++) {

                        if (empty($sched['schedule'][$i]['shift_id'])) {
                            continue;
                        }

                        if (empty($sched['schedule'][$i]['schedule_id'])) {
                            Schedule::create([
                                'user_id' => $sched['user_id'],
                                'shift_id' => $sched['schedule'][$i]['shift_id'],
                                'date' => $sched['schedule'][$i]['date'],
                                'week' => $sched['schedule'][$i]['week'],
                            ]);
                        } else {
                            Schedule::where('id', $sched['schedule'][$i]['schedule_id'])->update([
                                'user_id' => $sched['user_id'],
                                'shift_id' => $sched['schedule'][$i]['shift_id'],
                                'date' => $sched['schedule'][$i]['date'],
                                'week' => $sched['schedule'][$i]['week'],
                            ]);
                        }
                    }
                }
            }
            $success = true;
            $message = 'Submitted successfully.';
        } catch (\Throwable $th) {
            $message = "Failed to fetch schedules due to $th";
        }

        return response()->json([
            'success' => $success,
            'message' => $message
        ]);
    }
}
