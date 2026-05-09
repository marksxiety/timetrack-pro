<?php

namespace App\Http\Controllers;

use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Http\Request;

class OrganizationUnitController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_path' => ['required', 'string', 'max:50', 'unique:organization_units,unit_path'],
        ]);

        OrganizationUnit::create($validated);

        return redirect()->back()->with('message', 'Organization unit created successfully.');
    }

    public function update(Request $request, OrganizationUnit $organization_unit)
    {
        $validated = $request->validate([
            'unit_path' => ['required', 'string', 'max:50', 'unique:organization_units,unit_path,' . $organization_unit->id],
        ]);

        $organization_unit->update($validated);

        return redirect()->back()->with('message', 'Organization unit updated successfully.');
    }

    public function destroy(OrganizationUnit $organization_unit)
    {
        $validated = request()->validate([
            'reassign_to' => ['required', 'integer', 'exists:organization_units,id'],
        ]);

        $fallback = OrganizationUnit::find($validated['reassign_to']);

        if ($fallback->id === $organization_unit->id) {
            return redirect()->back()->withErrors(['reassign_to' => 'Cannot reassign users to the unit being deleted.']);
        }

        User::where('organization_unit_id', $organization_unit->id)
            ->update(['organization_unit_id' => $fallback->id]);

        $organization_unit->delete();

        return redirect()->back()->with('message', 'Organization unit deleted successfully. Affected users were reassigned to "' . $fallback->unit_path . '".');
    }
}
