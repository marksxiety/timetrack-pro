<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RequiredHours;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class RequiredHoursController extends Controller
{
    public function registerRequiredHours(Request $request)
    {

        $request->validate([
            'year' => 'required|integer|max_digits:4',
            'week' =>  'required|integer|max_digits:2',
            'required_hours' =>  'required|integer|max_digits:4'
        ]);

        $isRegistered = RequiredHours::where('year', $request->year)->where('week', $request->week)->where('organization_unit_id', Auth::user()->organization_unit_id)->exists();

        if ($isRegistered) {
            return redirect()->back()->withErrors([
                'year' => 'This year already has an entry.',
                'week' => 'This week already has an entry.',
            ]);
        }

        $roa = [
            'year' => $request->year,
            'week' => $request->week,
            'required_hours' => $request->required_hours,
            'organization_unit_id' =>  Auth::user()->organization_unit_id
        ];

        RequiredHours::create($roa);
        return redirect()->back()->with(['message' => 'Required Hours for week has been registered']);
    }

    public function registeredRequiredHours()
    {

        try {
            $requiredhours = RequiredHours::query()->where('organization_unit_id', Auth::user()->organization_unit_id)->orderBy('year', 'desc')->orderBy('week', 'desc')->get();

            return inertia('Maintenance/RequiredHours', [
                'requiredhours' => $requiredhours
            ]);
        } catch (\Throwable $th) {
            return inertia('Maintenance/RequiredHours', [
                'requiredhours' => [],
                'errors' => 'Failed to load registered Required Hours'
            ]);
        }
    }

    public function updateRequiredHour(Request $request, RequiredHours $requiredHours)
    {
        try {
            $data = $request->validate([
                'year' => 'required|integer|max_digits:4',
                'week' =>  'required|integer|max_digits:2',
                'required_hours' =>  'required|integer|max_digits:4'
            ]);

            $requiredHours->update($data);

            return redirect()->back()->with('message', 'Required Hour for Year ' . $request->year . ' and Week ' . $request->week . ' has been updated.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('message', 'Updating information failed. Please try again');
        }
    }
}
