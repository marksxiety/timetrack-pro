<?php

namespace App\Http\Controllers;

use App\Models\OrganizationUnit;
use App\Services\ReportDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function __construct(
        private ReportDataService $reportData
    ) {}

    public function reportPage(Request $request)
    {
        return inertia('Approver/Report', [
            'organizationUnits' => OrganizationUnit::select('id', 'unit_path')->orderBy('unit_path')->get(),
            'userRole' => Auth::user()->role,
        ]);
    }

    public function fetchReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'organization_unit_id' => 'nullable|integer|exists:organization_units,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        $orgUnitId = $user->role === 'admin'
            ? ($request->filled('organization_unit_id') ? (int) $request->organization_unit_id : null)
            : $user->organization_unit_id;

        $raw = $this->reportData->fetchRawReportData(
            $request->start_date,
            $request->end_date,
            $orgUnitId
        );

        return inertia('Approver/Report', [
            'report' => [
                'cards' => $this->reportData->buildStatCards($raw['requests']),
                'heatmap' => $this->reportData->buildHeatmapData($raw['requests']),
                'trends' => $this->reportData->buildOvertimeTrends($raw['filed'], $raw['requiredHours'], $raw['weeks']),
                'rankings' => $this->reportData->buildEmployeeRankings($raw['filed']),
                'cumulative' => $this->reportData->buildCumulativeOT($raw['filed']),
                'gauge' => $this->reportData->buildGaugeData($raw['filed'], $raw['requiredHours']),
                'status_pie' => $this->reportData->buildStatusPie($raw['requests']),
                'list' => $raw['requests']->values()->toArray(),
            ],
            'organizationUnits' => OrganizationUnit::select('id', 'unit_path')->orderBy('unit_path')->get(),
            'userRole' => $user->role,
        ]);
    }
}
