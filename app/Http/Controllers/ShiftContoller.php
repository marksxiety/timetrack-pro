<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shift;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class ShiftContoller extends Controller
{
    public function insertShiftCode(Request $request)
    {

        $rules = [
            'code' => 'unique:shift_codes|required|string|max:10',
        ];

        if ($request->timerequired) {
            $rules['start_time'] = 'required';
            $rules['end_time'] = 'required|after:start_time';
        }

        $info = [
            'code' => $request->code,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ];

        $request->validate($rules);
        Shift::create($info);

        return redirect()->back()->with('message', 'Shift code saved successfully.');
    }
    public function updateShiftCode(Request $request, Shift $shift)
    {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('shift_codes')->ignore($shift->id),
            ],
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);

        $shift->update($data);

        return redirect()->back()->with('message', 'Shift code updated successfully.');
    }

    public function registeredShiftCodes()
    {
        try {
            $shiftcodes = Shift::all();

            return inertia('Maintenance/ShiftCodes', [
                'shiftcodes' => $shiftcodes
            ]);
        } catch (\Throwable $th) {
            return inertia('Maintenance/ShiftCodes', [
                'shiftcodes' => [],
                'error' => 'Failed to fetch shift codes.'
            ]);
        }
    }

    public function deleteShiftCode(Shift $shift)
    {
        try {
            $shift->delete();
            return redirect()->back()->with('message', 'Shift code deleted successfully.');
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1451) {
                // Foreign key violation: shift is in use
                return redirect()->back()->withErrors([
                    'message' => 'Cannot delete this shift — it is currently assigned to a schedule.'
                ]);
            }

            return redirect()->back()->withErrors([
                'message' => 'Database error occurred during deletion.'
            ]);
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors([
                'message' => 'Shift code deletion unsuccessful.'
            ]);
        }
    }

    public function shiftCodeList()
    {
        $shiftCodes = Shift::all('id', 'code', 'start_time', 'end_time');
        return response()->json([
            'status' => 'success',
            'data' => $shiftCodes
        ]);
    }
}
