<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RequiredHours;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasScopedQueries;

class RequiredHoursController extends Controller
{
    use HasScopedQueries;

    public function registerRequiredHours(Request $request)
    {

        $request->validate([
            'year' => 'required|integer|max_digits:4',
            'week' =>  'required|integer|max_digits:2',
            'required_hours' =>  'required|integer|max_digits:4'
        ]);

        $orgUnitId = $this->getOrgUnitId();

        $isRegisteredQuery = RequiredHours::where('year', $request->year)->where('week', $request->week);

        if ($orgUnitId !== null) {
            $isRegisteredQuery->where('organization_unit_id', $orgUnitId);
        }

        $isRegistered = $isRegisteredQuery->exists();

        if ($isRegistered) {
            return redirect()->back()->withErrors([
                'year' => 'This year already has an entry.',
                'week' => 'This week already has an entry.',
            ]);
        }

        $limit = [
            'year' => $request->year,
            'week' => $request->week,
            'required_hours' => $request->required_hours,
            'organization_unit_id' =>  $orgUnitId
        ];

        RequiredHours::create($limit);
        return redirect()->back()->with(['message' => 'Required Hours for week has been registered']);
    }

    public function registeredRequiredHours()
    {

        try {
            $orgUnitId = $this->getOrgUnitId();
            $query = RequiredHours::query()->orderBy('year', 'desc')->orderBy('week', 'desc');

            if ($orgUnitId !== null) {
                $query->where('organization_unit_id', $orgUnitId);
            }

            $requiredhours = $query->get();

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
