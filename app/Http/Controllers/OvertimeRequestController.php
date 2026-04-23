<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OvertimeRequest;
use App\Models\Schedule;
use App\Models\RequiredHours;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class OvertimeRequestController extends Controller
{
    public function insertOvertimeRequest(Request $request)
    {
        $rules = [
            'employee_schedule_id' => 'exists:schedules,id|required',
            'date' => 'required|date_format:Y-m-d',
            'reason' => 'required|string|min:1',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
        ];

        $validator = Validator::make($request->all(), $rules);
        $errors = $validator->errors();

        if ($errors->any()) {
            return redirect()->back()->withErrors($errors)->withInput();
        }

        // Parse submitted overtime start/end into Carbon instances
        $submittedStart = Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . trim($request->start_time));
        $submittedEnd   = Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . trim($request->end_time));

        // Load the schedule and its shift from the database (not from the request)
        $schedule = Schedule::with('shift')->findOrFail($request->employee_schedule_id);
        $shift = $schedule->shift;

        // Determine if the schedule has a shift with actual time values
        $hasShiftTimes = $shift && $shift->start_time && $shift->end_time;

        if ($hasShiftTimes) {
            // Reject swapped/inverted times when a shift exists: end time must be strictly after start time
            // e.g., 8AM-6AM is invalid — crosses midnight ambiguously and always wraps through the shift
            if ($submittedEnd->lessThanOrEqualTo($submittedStart)) {
                $errors->add('end_time', 'End time must be after start time.');
                return redirect()->back()->withErrors($errors)->withInput();
            }

            // Parse shift start/end into Carbon instances on the schedule date
            $shiftStart = Carbon::createFromFormat('Y-m-d H:i:s', $request->date . ' ' . $shift->start_time);
            $shiftEnd   = Carbon::createFromFormat('Y-m-d H:i:s', $request->date . ' ' . $shift->end_time);

            // Detect night shift: raw end time is earlier than raw start time (e.g., 10PM–6AM)
            $isNightShift = $shiftEnd->lessThan($shiftStart);

            if ($isNightShift) {
                // Night shift crosses midnight, so shift the end forward by 1 day
                // e.g., Jan 1 10PM → Jan 2 6AM
                $shiftEnd = $shiftEnd->copy()->addDay();

                // For night shifts, AM submitted times (e.g., 2AM-4AM) are logically
                // in the "next day" portion of the shift. Only shift AM times (before noon)
                // forward by 1 day so they compare correctly against the adjusted shiftEnd.
                // PM times before shift start (e.g., 9PM before 10PM) stay on the same day.
                if ($submittedStart->hour < 12) {
                    $submittedStart = $submittedStart->copy()->addDay();
                    $submittedEnd   = $submittedEnd->copy()->addDay();
                }
            }

            // Classify the overtime: must be entirely BEFORE or entirely AFTER the shift
            // Touching the shift boundary is allowed (e.g., 7AM-8AM before 8AM-6PM is valid)
            $isBeforeShift = $submittedEnd->lessThanOrEqualTo($shiftStart);
            $isAfterShift  = $submittedStart->greaterThanOrEqualTo($shiftEnd);

            // Reject if the overtime is neither before nor after (overlaps, wraps, or straddles the shift)
            if (!$isBeforeShift && !$isAfterShift) {
                $errors->add('start_time', 'Overtime must be entirely before or after the scheduled shift.');
                $errors->add('end_time', 'Overtime must be entirely before or after the scheduled shift.');
                return redirect()->back()->withErrors($errors)->withInput();
            }
        }

        // Calculate overtime duration in decimal hours
        $hours = $this->calculateOvertimeHours($submittedStart, $submittedEnd);

        // Enforce the minimum overtime hours from config
        $minimumHours = (float) $this->getMinimumOvertimeHours();
        if ((float) $hours < $minimumHours) {
            $errors->add('start_time', "Overtime request must be at least {$minimumHours} hour(s).");
            $errors->add('end_time', "Overtime request must be at least {$minimumHours} hour(s).");
            return redirect()->back()->withErrors($errors)->withInput();
        }

        OvertimeRequest::create([
            'employee_schedule_id' => $request->employee_schedule_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'hours' => $hours,
            'reason' => $request->reason,
        ]);

        return redirect()->back()->with(['message' => 'Overtime Request has been filed!']);
    }

    public function calculateOvertimeHours(Carbon $start, Carbon $end)
    {
        $adjustedEnd = $end->copy();

        if ($adjustedEnd->lessThan($start)) {
            $adjustedEnd->addDay();
        }

        $minutes = $start->diffInMinutes($adjustedEnd);
        $decimalHours = $minutes / 60;
        return number_format($decimalHours, 2);
    }


    public function updateOvertimeRequestStatus(Request $request)
    {
        try {
            $rules = [];
            $updateData = [];

            // force the user to input remarks if the status is disapproved or declined
            // this will provde the user proper reason why their overtime request has been declined or disapproved
            if (in_array($request->update_status, ['DISAPPROVED', 'DECLINED'])) {
                $rules['remarks'] = 'required|string|min:10';
            } else {
                $rules['current_status'] = ['required', Rule::in(['PENDING', 'APPROVED'])];

                // require only the reason if the status is PENDING, this means that 
                // the user is updating only the reason and comes from the approver event
                if ($request->update_status === 'PENDING') {
                    $rules['reason'] = 'required|string|min:1';
                }
            }

            $messages = [
                'current_status.in' => 'The selected status is invalid. Only PENDING or APPROVED are allowed.',
                'current_status.required' => 'The current status field is required.'
            ];

            $validate = Validator::make($request->all(), $rules, $messages);

            if ($validate->fails()) {
                return redirect()->back()
                    ->withErrors($validate)
                    ->withInput();
            }

            if ($request->update_status === 'PENDING') {
                $rules = [
                    'employee_schedule_id' => 'exists:schedules,id|required',
                    'date' => 'required|date_format:Y-m-d',
                    'reason' => 'required|string|min:1',
                    'start_time' => 'required|date_format:H:i',
                    'end_time' => 'required|date_format:H:i',
                ];

                $validator = Validator::make($request->all(), $rules);
                $errors = $validator->errors();

                if ($errors->any()) {
                    return redirect()->back()->withErrors($errors)->withInput();
                }

                // Parse submitted overtime start/end into Carbon instances
                $submittedStart = Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . trim($request->start_time));
                $submittedEnd   = Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . trim($request->end_time));

                // Load the schedule and its shift from the database (not from the request)
                $schedule = Schedule::with('shift')->findOrFail($request->employee_schedule_id);
                $shift = $schedule->shift;

                // Determine if the schedule has a shift with actual time values
                $hasShiftTimes = $shift && $shift->start_time && $shift->end_time;

                if ($hasShiftTimes) {
                    // Reject swapped/inverted times when a shift exists
                    if ($submittedEnd->lessThanOrEqualTo($submittedStart)) {
                        $errors->add('end_time', 'End time must be after start time.');
                        return redirect()->back()->withErrors($errors)->withInput();
                    }

                    // Parse shift start/end into Carbon instances on the schedule date
                    $shiftStart = Carbon::createFromFormat('Y-m-d H:i:s', $request->date . ' ' . $shift->start_time);
                    $shiftEnd   = Carbon::createFromFormat('Y-m-d H:i:s', $request->date . ' ' . $shift->end_time);

                    // Detect night shift: raw end time is earlier than raw start time (e.g., 10PM–6AM)
                    $isNightShift = $shiftEnd->lessThan($shiftStart);

                    if ($isNightShift) {
                        // Night shift crosses midnight, so shift the end forward by 1 day
                        $shiftEnd = $shiftEnd->copy()->addDay();

                        // For night shifts, AM submitted times (e.g., 2AM-4AM) are logically
                        // in the "next day" portion of the shift. Only shift AM times (before noon)
                        // forward by 1 day so they compare correctly against the adjusted shiftEnd.
                        if ($submittedStart->hour < 12) {
                            $submittedStart = $submittedStart->copy()->addDay();
                            $submittedEnd   = $submittedEnd->copy()->addDay();
                        }
                    }

                    // Classify the overtime: must be entirely BEFORE or entirely AFTER the shift
                    $isBeforeShift = $submittedEnd->lessThanOrEqualTo($shiftStart);
                    $isAfterShift  = $submittedStart->greaterThanOrEqualTo($shiftEnd);

                    // Reject if the overtime is neither before nor after
                    if (!$isBeforeShift && !$isAfterShift) {
                        $errors->add('start_time', 'Overtime must be entirely before or after the scheduled shift.');
                        $errors->add('end_time', 'Overtime must be entirely before or after the scheduled shift.');
                        return redirect()->back()->withErrors($errors)->withInput();
                    }
                }

                // Calculate overtime duration in decimal hours
                $hours = $this->calculateOvertimeHours($submittedStart, $submittedEnd);

                // Enforce the minimum overtime hours from config
                $minimumHours = (float) $this->getMinimumOvertimeHours();
                if ((float) $hours < $minimumHours) {
                    $errors->add('start_time', "Overtime request must be at least {$minimumHours} hour(s).");
                    $errors->add('end_time', "Overtime request must be at least {$minimumHours} hour(s).");
                    return redirect()->back()->withErrors($errors)->withInput();
                }

                $updateData['employee_schedule_id'] = $request->employee_schedule_id;
                $updateData['start_time'] = $request->start_time;
                $updateData['end_time'] = $request->end_time;
                $updateData['hours'] = $hours;
                $updateData['reason'] = $request->reason;
            }

            $updateData['status']  = $request->update_status;
            $updateData['remarks'] = $request->remarks;

            // only update reason if status is PENDING
            if ($request->update_status === 'PENDING') {
                $updateData['reason'] = $request->reason;
            }

            OvertimeRequest::where('id', $request->id)->update($updateData);
            return redirect()->back();
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors("Cancelation failed due to $th");
        }
    }


    public function fetchOvertimeRequestsBySession(Request $request)
    {

        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $actualyear = Carbon::now()->year;
        $actualmonth = Carbon::now()->month;
        $actualday = Carbon::now()->day;

        $overtimelist = [];
        $overtime = null;
        $message = '';
        $stats = [
            'total_overtime_hours' => 0,
            'approved_requests' => 0,
            'pending_requests' => 0,
            'rejected_requests' => 0,
        ];
        try {
            $allOvertimes = OvertimeRequest::whereHas('schedule', function ($query) {
                $query->where('user_id', Auth::id());
            })->get();

            foreach ($allOvertimes as $ot) {
                $status = strtoupper($ot->status);
                if ($status === 'APPROVED' || $status === 'FILED') {
                    $stats['approved_requests']++;
                    $stats['total_overtime_hours'] += (float) $ot->hours;
                }
                if ($status === 'PENDING') {
                    $stats['pending_requests']++;
                }
                if ($status === 'DISAPPROVED') {
                    $stats['rejected_requests']++;
                }
            }

            $stats['total_overtime_hours'] = rtrim(rtrim(number_format($stats['total_overtime_hours'], 2), '0'), '.');

            $recentRequestsList = [];
            $recentOvertimes = OvertimeRequest::with(['schedule' => function ($query) {
                $query->select('id', 'week', 'date', 'user_id', 'shift_id');
            }, 'schedule.shift' => function ($query) {
                $query->select('id', 'code', 'start_time', 'end_time');
            }])
                ->whereHas('schedule', function ($query) {
                    $query->where('user_id', Auth::id());
                })
                ->select('id', 'employee_schedule_id', 'start_time', 'end_time', 'hours', 'reason', 'remarks', 'status', 'created_at')
                ->limit(5)
                ->orderBy('updated_at', 'desc')
                ->get();

            foreach ($recentOvertimes as $overtime) {
                $recentRequestsList[] = [
                    'week' => $overtime->schedule->week ?? 'N/A',
                    'date' => $overtime->schedule->date ?? 'N/A',
                    'employee_schedule_id' => $overtime->employee_schedule_id,
                    'shift_code' => $overtime->schedule->shift->code ?? 'No Shift',
                    'shift_start_time' => $overtime->schedule->shift && $overtime->schedule->shift->start_time
                        ? Carbon::createFromFormat('H:i:s', $overtime->schedule->shift->start_time)->format('h:i A')
                        : '--',
                    'shift_end_time' => $overtime->schedule->shift && $overtime->schedule->shift->end_time
                        ? Carbon::createFromFormat('H:i:s', $overtime->schedule->shift->end_time)->format('h:i A')
                        : '--',
                    'id' => $overtime->id,
                    'start_time' => $overtime->start_time ? Carbon::createFromFormat('H:i:s', $overtime->start_time)->format('h:i A') : 'N/A',
                    'end_time' => $overtime->end_time ? Carbon::createFromFormat('H:i:s', $overtime->end_time)->format('h:i A') : 'N/A',
                    'hours' => $overtime->hours,
                    'reason' => $overtime->reason,
                    'remarks' => $overtime->remarks,
                    'status' => $overtime->status,
                    'created_at' => $overtime->created_at ? Carbon::parse($overtime->created_at)->setTimezone('Asia/Manila')->format('M j, Y h:i A') : 'N/A'
                ];
            }

            $overtimes = OvertimeRequest::with(['schedule' => function ($query) {
                $query->select('id', 'week', 'date', 'user_id', 'shift_id');
            }, 'schedule.shift' => function ($query) {
                $query->select('id', 'code', 'start_time', 'end_time');
            }])
                ->whereHas('schedule', function ($query) use ($year, $month) {
                    $query->where('user_id', Auth::id())->whereYear('date', $year)->whereMonth('date', $month);
                })
                ->select('id', 'employee_schedule_id', 'start_time', 'end_time', 'hours', 'reason', 'remarks', 'status', 'created_at')
                ->limit(5)
                ->orderBy('updated_at', 'desc')
                ->get();

            foreach ($overtimes as $overtime) {
                $overtimelist[] = [
                    'week' => $overtime->schedule->week ?? 'N/A',
                    'date' => $overtime->schedule->date ?? 'N/A',
                    'employee_schedule_id' => $overtime->employee_schedule_id,
                    'shift_code' => $overtime->schedule->shift->code ?? 'No Shift',
                    'shift_start_time' => $overtime->schedule->shift && $overtime->schedule->shift->start_time
                        ? Carbon::createFromFormat('H:i:s', $overtime->schedule->shift->start_time)->format('h:i A')
                        : '--',
                    'shift_end_time' => $overtime->schedule->shift && $overtime->schedule->shift->end_time
                        ? Carbon::createFromFormat('H:i:s', $overtime->schedule->shift->end_time)->format('h:i A')
                        : '--',
                    'id' => $overtime->id,
                    'start_time' => $overtime->start_time ? Carbon::createFromFormat('H:i:s', $overtime->start_time)->format('h:i A') : 'N/A',
                    'end_time' => $overtime->end_time ? Carbon::createFromFormat('H:i:s', $overtime->end_time)->format('h:i A') : 'N/A',
                    'hours' => $overtime->hours,
                    'reason' => $overtime->reason,
                    'remarks' => $overtime->remarks,
                    'status' => $overtime->status,
                    'created_at' => $overtime->created_at ? Carbon::parse($overtime->created_at)->setTimezone('Asia/Manila')->format('M j, Y h:i A') : 'N/A'
                ];
            }


            $success = true;
        } catch (\Throwable $th) {
            $success = false;
            $message = "Fetching Failed due to $th";
        }

        return inertia('Employee/Index', [
            'info' => [
                'overtimelist' => $overtimelist,
                'recentRequestsList' => $recentRequestsList
            ],
            'stats' => $stats,
            'payload' => [
                'year' => $year,
                'month' => $month,
                'actual' => [
                    'year' => $actualyear,
                    'month' => $actualmonth,
                    'day' => $actualday
                ]
            ],
            'success' => $success,
            'message' => $message
        ]);
    }

    public function fetchTotalOvertimeRequests(Request $request)
    {
        $week = $request->input('week', $this->currentWeekSundayBased());
        $year = $request->input('year', Carbon::now()->year);
        $message = '';
        try {
            $required_registered_hours = DB::table('required_hours')->where('year', $year)->where('week', $week)->orderBy('updated_at', 'desc')->select('required_hours.required_hours as hours')->first();
            $requests = DB::table('overtime_requests')
                ->join('schedules', 'schedules.id', '=', 'overtime_requests.employee_schedule_id')
                ->join('users', 'users.id', '=', 'schedules.user_id')
                ->select('schedules.date', 'schedules.week', 'overtime_requests.status', 'overtime_requests.remarks', 'overtime_requests.reason', 'users.name', 'overtime_requests.hours')
                ->whereYear('schedules.date', $year)
                ->where('schedules.week', $week)
                ->where('users.organization_unit_id', Auth::user()->organization_unit_id)
                ->get();

            $total_filed = 0;
            $total_approved = 0;
            $total_pending = 0;
            $total_declined = 0;
            $total_canceled = 0;
            $total_disapproved = 0;
            $total_requests = 0;
            $total_hours = 0;
            $required_hours = $required_registered_hours->hours ?? 0;

            $result = [
                [
                    'name' => 'PENDING',
                    'value' => 0,
                    'remarks' => []
                ],
                [
                    'name' => 'FILED',
                    'value' => 0,
                    'remarks' => []
                ],
                [
                    'name' => 'APPROVED',
                    'value' => 0,
                    'remarks' => []
                ],
                [
                    'name' => 'DECLINED',
                    'value' => 0,
                    'remarks' => []
                ],
                [
                    'name' => 'CANCELED',
                    'value' => 0,
                    'remarks' => []
                ],
                [
                    'name' => 'DISAPPROVED',
                    'value' => 0,
                    'remarks' => []
                ],
            ];

            foreach ($requests as $request) {
                $status = strtoupper($request->status); // Normalize to uppercase

                // ====== build the data for pie graph ======
                for ($index = 0; $index < count($result); $index++) {
                    if ($request->status === $result[$index]['name']) {
                        $result[$index]['value']++;

                        if ($request->remarks) {
                            $result[$index]['remarks'][] = $request->remarks;
                        }
                    }
                }

                // ====== consolidate all the countings for card dispaly ======
                $total_requests++;
                switch ($status) {
                    case 'FILED':
                        $total_filed++;
                        $total_hours += (float)$request->hours ?? 0;
                        break;
                    case 'APPROVED':
                        $total_approved++;
                        $total_hours += (float)$request->hours ?? 0;
                        break;
                    case 'PENDING':
                        $total_pending++;
                        break;
                    case 'DECLINED':
                        $total_declined++;
                        break;
                    case 'CANCELED':
                        $total_canceled++;
                        break;
                    case 'DISAPPROVED':
                        $total_disapproved++;
                        break;
                }
            }


            // remove the status that does not have any filing.
            // this is to display only the data that has specific value to avoid 
            // including legend in a pie graph that doesn't have a value
            for ($counter = 0; $counter < count($result); $counter++) {
                if ($result[$counter]['value'] === 0) {
                    // after unsetting, reindex and decrement so it wont 
                    // skip the next loop
                    unset($result[$counter]);
                    $result = array_values($result);
                    $counter--;
                }
            }

            // ============= FORMAT FOR BREAKDOWN OVERTIME =============
            // get the first day of the week then get the proceeding days
            $startOfWeek = CarbonImmutable::createFromDate($year, 1, 1, 'Asia/Manila')
                ->startOfWeek(Carbon::SUNDAY)
                ->addWeeks($week - 1);

            // consolidate the dates that will used to join in requests data (format it in Y-m-d since that is the format of date in db)
            $dates = [];
            for ($i = 0; $i < 7; $i++) {
                $dates[] = $startOfWeek->addDays($i)->format('Y-m-d');
            }

            $breakdown = [];

            foreach ($requests as $req) {
                // Find if this name already exists in breakdown
                $index = array_search($req->name, array_column($breakdown, 'name'));

                if ($index === false) {
                    // Create a new entry with 0 hours for all dates
                    $dataPoints = array_fill(0, count($dates), 0);

                    // Fill in the hours for the matching date
                    foreach ($dates as $i => $date) {
                        if ($date === $req->date && in_array($req->status, ['APPROVED', 'FILED'])) {
                            $dataPoints[$i] += $req->hours;
                        }
                    }

                    $breakdown[] = [
                        'name'  => $req->name,
                        'type'  => 'bar',
                        'stack' => 'total',
                        'data'  => $dataPoints
                    ];
                } else {
                    // Update existing entry
                    foreach ($dates as $i => $date) {
                        if ($date === $req->date && in_array($req->status, ['APPROVED', 'FILED'])) {
                            $breakdown[$index]['data'][$i] += $req->hours;
                        }
                    }
                }
            }

            $total_computed_hours = array_fill(0, count($dates), 0);

            foreach ($breakdown as $br) {
                foreach ($br['data'] as $i => $hours) {
                    $total_computed_hours[$i] += $hours;
                }
            }

            $rounded_total_computed_hours = array_map(function ($num) {
                return round($num, 2);
            }, $total_computed_hours);

            $breakdown[] = [
                'name'  => 'Total',
                'type'  => 'line',
                'data'  => $rounded_total_computed_hours,
                'smooth' => true
            ];

            $success = true;
        } catch (\Throwable $th) {
            $success = false;
            $message = "Fetching Failed due to $th";
        }

        $recentRequests = OvertimeRequest::with(['schedule' => function ($query) {
            $query->select('id', 'week', 'date', 'user_id', 'shift_id');
        }, 'schedule.shift' => function ($query) {
            $query->select('id', 'code', 'start_time', 'end_time');
        }, 'schedule.user' => function ($query) {
            $query->select('id', 'name', 'employeeid', 'avatar');
        }])
            ->whereHas('schedule', function ($query) {
                $query->whereHas('user', function ($userQuery) {
                    $userQuery->where('organization_unit_id', Auth::user()->organization_unit_id);
                });
            })
            ->select('id', 'employee_schedule_id', 'start_time', 'end_time', 'hours', 'reason', 'remarks', 'status', 'created_at')
            ->limit(10)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($ot) {
                return [
                    'id' => $ot->id,
                    'date' => $ot->schedule->date ?? 'N/A',
                    'shift_code' => $ot->schedule->shift->code ?? 'N/A',
                    'hours' => $ot->hours,
                    'status' => $ot->status,
                    'reason' => $ot->reason,
                    'user_name' => $ot->schedule->user->name ?? 'Unknown',
                    'avatar_url' => $ot->schedule->user->avatar ? Storage::url($ot->schedule->user->avatar) : null,
                    'created_at' => $ot->created_at ? Carbon::parse($ot->created_at)->setTimezone('Asia/Manila')->format('M j, Y h:i A') : 'N/A',
                ];
            });

        return inertia('Approver/Index', [
            'info' => [
                'result' => [
                    'requests' => $result,
                    'breakdown' => $breakdown,
                    'totals' => [
                        'FILED' => $total_filed,
                        'APPROVED' => $total_approved,
                        'PENDING' => $total_pending,
                        'DECLINED' => $total_declined,
                        'CANCELED' => $total_canceled,
                        'DISAPPROVED' => $total_disapproved,
                        'TOTAL_REQUESTS' => $total_requests,
                        'TOTAL_HOURS' => $total_hours,
                        'REQUIRED_HOURS' => $required_hours,
                    ]
                ],
                'recentRequests' => $recentRequests,
                'payload' => [
                    'year' => $year,
                    'week' => $week
                ],
                'test' => [
                    'days' => $dates,
                    'breakdown' => $breakdown
                ]
            ],
            'success' => $success,
            'message' => $message
        ]);
    }

    public function fetchOvertimeRequestsViaStatus(Request $request)
    {
        $week = $request->input('week', $this->currentWeekSundayBased());
        $year = $request->input('year', Carbon::now()->year);
        $status = $request->input('status', '');
        $page = $request->input('page', null);

        $overtimelist = [];
        $overtime_requests = [];
        $message = '';
        $remaining_hours = 0;
        try {
            $overtime_requests = DB::table('overtime_requests')->join('schedules', 'schedules.id', '=', 'overtime_requests.employee_schedule_id')->join('users', 'users.id', '=', 'schedules.user_id')
                ->leftJoin('shift_codes', 'shift_codes.id', '=', 'schedules.shift_id')
                ->select(
                    'overtime_requests.id as request_id',
                    'users.id as user_id',
                    'users.name',
                    'users.employeeid',
                    'users.role',
                    'users.email',
                    'overtime_requests.start_time',
                    'overtime_requests.end_time',
                    'schedules.date',
                    'schedules.week',
                    'shift_codes.code as shift_code',
                    'shift_codes.start_time as shift_start',
                    'shift_codes.end_time as shift_end',
                    'overtime_requests.start_time',
                    'overtime_requests.end_time',
                    'overtime_requests.hours',
                    'overtime_requests.status',
                    'overtime_requests.reason',
                    'overtime_requests.remarks',
                    'overtime_requests.created_at'
                )->where('status', $status)->whereYear('schedules.date', $year)->where('schedules.week', $week)->where('users.organization_unit_id', Auth::user()->organization_unit_id)->orderBy('users.employeeid')->orderBy('overtime_requests.created_at')->get();

            // fetch the registered hours limit on the specific year and week
            $required_registered_hours = DB::table('required_hours')->where('year', $year)->where('week', $week)->where('organization_unit_id', Auth::user()->organization_unit_id)->orderBy('updated_at', 'desc')->select('required_hours.required_hours as hours')->first();
            $remaining_hours = $this->computeRemainingHours($year, $week, $required_registered_hours->hours ?? 0);

            foreach ($overtime_requests as $overtime) {
                // create instance on timestamps
                $overtime_start = Carbon::createFromFormat('H:i:s', $overtime->start_time);
                $overtime_end = Carbon::createFromFormat('H:i:s', $overtime->end_time);

                $schedule_start = $overtime->shift_start === null ? '--' :  Carbon::createFromFormat('H:i:s', $overtime->shift_start);
                $schedule_end = $overtime->shift_start === null ? '--' :  Carbon::createFromFormat('H:i:s', $overtime->shift_end);

                $overtime_created = Carbon::createFromFormat('Y-m-d H:i:s', $overtime->created_at);

                $overtimelist[] = [
                    'id' => $overtime->request_id,
                    'user' => [
                        'name' => $overtime->name,
                        'employee_id' => $overtime->employeeid,
                        'email' => $overtime->email,
                        'role' => $overtime->role,
                    ],
                    'schedule' => [
                        'date' => $overtime->date,
                        'week' => $overtime->week,
                        'shift_code' => $overtime->shift_code ?? 'N/A',
                        'shift_start' => $schedule_start === '--' ? '--' : $schedule_start->format('h:i A'),
                        'shift_end' => $schedule_end === '--' ? '--' : $schedule_end->format('h:i A'),
                    ],
                    'overtime' => [
                        'start_time' => $overtime_start->format('h:i A'),
                        'end_time' =>  $overtime_end->format('h:i A'),
                        'hours' => $overtime->hours,
                        'status' => $overtime->status,
                        'reason' => $overtime->reason,
                        'remarks' => $overtime->remarks,
                        'created_at' => $overtime_created->format('l, jS \of F Y, h:i:s A')
                    ]
                ];
            }
            $success = true;
        } catch (\Throwable $th) {
            $success = false;
            $message = "Fetching Failed due to $th";
        }

        return inertia($page, [
            'info' => [
                'requests' => $overtimelist,
                'payload' => [
                    'year' => $year,
                    'week' => $week,
                    'status' => $status,
                    'page' => $page
                ],
                'hours' => [
                    'limit' => $required_registered_hours->hours ?? 0,
                    'remaining' => $remaining_hours
                ]
            ],
            'success' => $success,
            'message' => $message
        ]);
    }

    public function currentWeekSundayBased($date = null)
    {
        $date = $date ?: Carbon::now();
        $firstDayOfYear = Carbon::create($date->year, 1, 1);

        // get the number of days passed since Jan 1
        $pastDays = $firstDayOfYear->diffInDays($date);

        // add firstDayOfYear weekday (0=Sunday, 6=Saturday)
        $weekNumber = (int) ceil(($pastDays + $firstDayOfYear->dayOfWeek + 1) / 7);

        return $weekNumber;
    }

    public function computeRemainingHours($year, $week, $required_hours)
    {
        $total_hours = OvertimeRequest::where('status', 'APPROVED')
            ->whereHas('schedule', function ($scheduleQuery) use ($year, $week) {
                $scheduleQuery->whereYear('date', $year)
                    ->where('week', $week)
                    ->whereHas('user', function ($userQuery) {
                        $userQuery->where('organization_unit_id', Auth::user()->organization_unit_id);
                    });
            })
            ->sum('hours');

        return ($required_hours ?? 0) - (float) $total_hours;
    }

    public function fetchOvertimeRequestsViaDateRange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $weeks = [];
        $current = $startDate->copy()->startOfWeek(Carbon::SUNDAY);

        while ($current->lessThanOrEqualTo($endDate)) {
            $weeks[] = [
                'week' => $current->weekOfYear,
                'date' => $current->toDateString()
            ];
            $current->addWeek();
        }

        // Extract week numbers for the query
        $weekNumbers = collect($weeks)->pluck('week');

        $registered_limit_hours = RequiredHours::select('week', 'required_hours')
            ->whereIn('week', $weekNumbers)
            ->where('organization_unit_id', Auth::user()->organization_unit_id)
            ->get()
            ->map(function ($item) use ($weeks) {
                // Match week with date
                $date = collect($weeks)->firstWhere('week', $item->week)['date'] ?? null;
                return [
                    'week' => $item->week,
                    'date' => $date,
                    'required_hours' => $item->required_hours,
                ];
            });


        $requests = OvertimeRequest::with(['schedule.user'])
            ->whereHas('schedule', function ($query) use ($request) {
                $query->whereBetween('date', [$request->start_date, $request->end_date])
                    ->whereHas('user', fn($q) => $q->where('role', 'employee')->where('organization_unit_id', $request->unit));
            })
            ->get()
            ->map(function ($req) {
                return [
                    'id' => $req->id,
                    'hours' => $req->hours,
                    'reason' => $req->reason,
                    'status' => $req->status,
                    'date' => $req->schedule->date,
                    'week' => $req->schedule->week,
                    'user_name' => $req->schedule->user->name,
                    'user_id' => $req->schedule->user->employeeid,
                    'user_avatar' => $req->schedule->user->avatar ? Storage::url($req->schedule->user->avatar) : null,
                ];
            });


        return inertia('Approver/Report', [
            'requests' => [
                'list' => $requests,
                'required_hours' => $registered_limit_hours,
            ],
            'weeks' => $weeks
        ]);
    }
    public function fetchOvertimeRequestOfEmployee(Request $request)
    {
        $week = $request->input('week', '');
        $status = $request->input('status', 'ALL');
        $search = $request->input('search', '');
        $sort = $request->input('sort', 'date_desc');
        $message = '';
        try {

            $requests = OvertimeRequest::with(['schedule.user', 'schedule.shift'])
                ->join('schedules', 'overtime_requests.employee_schedule_id', '=', 'schedules.id')
                ->whereHas('schedule.user', fn($q) => $q->where('id', Auth::id()))
                ->when($week, fn($q) => $q->where('schedules.week', $week))
                ->when($status !== 'ALL', fn($q) => $q->where('overtime_requests.status', $status))
                ->when($search, function ($query) use ($search) {
                    return $query->where(function ($q) use ($search) {
                        $q->where('overtime_requests.reason', 'like', '%' . $search . '%')
                            ->orWhere('overtime_requests.remarks', 'like', '%' . $search . '%')
                            ->orWhere('schedules.date', 'like', '%' . $search . '%')
                            ->orWhere('schedules.week', 'like', '%' . $search . '%');
                    });
                })
                ->when($sort === 'date_asc', fn($q) => $q->orderBy('schedules.date', 'asc')
                    ->orderBy('overtime_requests.updated_at', 'asc'))
                ->when($sort === 'date_desc', fn($q) => $q->orderBy('schedules.date', 'desc')
                    ->orderBy('overtime_requests.updated_at', 'desc'))
                ->when($sort === 'status_asc', fn($q) => $q->orderBy('overtime_requests.status', 'asc')
                    ->orderBy('schedules.date', 'desc'))
                ->when($sort === 'status_desc', fn($q) => $q->orderBy('overtime_requests.status', 'desc')
                    ->orderBy('schedules.date', 'desc'))
                ->when(!in_array($sort, ['date_asc', 'date_desc', 'status_asc', 'status_desc']),
                    fn($q) => $q->orderBy('overtime_requests.updated_at', 'desc'))
                ->select('overtime_requests.*')
                ->paginate(10)
                ->appends($request->query());

            // Transform each item while keeping pagination
            $requests->getCollection()->transform(function ($req) {
                return [
                    'id'      => $req->id,
                    'employee_schedule_id' => $req->employee_schedule_id,
                    'created_at' => $req->created_at ? $req->created_at->format('M d, Y h:i A') : 'N/A',
                    'shift'   => $req->schedule->shift ? $req->schedule->shift->code : 'N/A',
                    'shift_start_time' => $req->schedule->shift && $req->schedule->shift->start_time
                        ? Carbon::createFromFormat('H:i:s', $req->schedule->shift->start_time)->format('h:i A')
                        : '--',
                    'shift_end_time' => $req->schedule->shift && $req->schedule->shift->end_time
                        ? Carbon::createFromFormat('H:i:s', $req->schedule->shift->end_time)->format('h:i A')
                        : '--',
                    'start_time' => $req->start_time ? Carbon::createFromFormat('H:i:s', $req->start_time)->format('h:i A') : 'N/A',
                    'end_time' => $req->end_time ? Carbon::createFromFormat('H:i:s', $req->end_time)->format('h:i A') : 'N/A',
                    'date'    => $req->schedule->date,
                    'week'    => $req->schedule->week,
                    'status'  => $req->status,
                    'hours'   => $req->hours,
                    'reason'  => $req->reason,
                    'remarks' => $req->remarks,
                ];
            });


            $success = true;
        } catch (\Throwable $th) {
            $success = false;
            $message = "Fetching Failed due to $th";
        }

        return inertia('Employee/Request', [
            'info' => [
                'requests' => $requests
            ],
            'payload' => [
                'week' => $week,
                'status' => $status,
                'sort' => $sort,
                'search' => $search
            ],
            'success' => $success,
            'message' => $message
        ]);
    }

    public function fetchOvertimeHeatmap(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $statuses = $request->input('statuses', ['APPROVED']);

        if (!$startDate || !$endDate) {
            $endDate = Carbon::now()->toDateString();
            $startDate = Carbon::now()->subDays(365)->toDateString();
        }

        $firstYear = OvertimeRequest::whereHas('schedule', function ($query) {
            $query->where('user_id', Auth::id());
        })
            ->whereIn('status', (array) $statuses)
            ->join('schedules', 'schedules.id', '=', 'overtime_requests.employee_schedule_id')
            ->min(DB::raw('YEAR(schedules.date)'));

        if (!$firstYear) {
            return response()->json([
                'years' => [Carbon::now()->year],
                'data' => [],
            ]);
        }

        $years = range((int) $firstYear, Carbon::now()->year);

        $data = OvertimeRequest::whereIn('status', (array) $statuses)
            ->whereHas('schedule', function ($query) use ($startDate, $endDate) {
                $query->where('user_id', Auth::id())
                    ->whereBetween('date', [$startDate, $endDate]);
            })
            ->join('schedules', 'schedules.id', '=', 'overtime_requests.employee_schedule_id')
            ->select('schedules.date', DB::raw('SUM(overtime_requests.hours) as total_hours'))
            ->groupBy('schedules.date')
            ->orderBy('schedules.date')
            ->get()
            ->mapWithKeys(fn($item) => [
                $item->date => (float) $item->total_hours,
            ]);

        return response()->json([
            'years' => $years,
            'data'  => $data,
        ]);
    }

    private function getMinimumOvertimeHours(): float
    {
        $path = base_path('setup/config.json');
        if (file_exists($path)) {
            $config = json_decode(file_get_contents($path), true);
            $value = (float) ($config['minimum_overtime_hours'] ?? 1);

            // Enforce 15-minute (0.25) increments: 0.25, 0.50, 0.75, 1.00, 1.25, etc.
            // Values like 0.20 or 0.33 are invalid and fall back to default.
            if ($value <= 0 || abs(fmod($value, 0.25)) > 0.001) {
                return 1.0;
            }

            return $value;
        }
        return 1.0;
    }


}
