<?php

namespace App\Http\Controllers;

use App\Models\OrganizationUnit;
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
        $organization_unit->delete();

        return redirect()->back()->with('message', 'Organization unit deleted successfully.');
    }
}
