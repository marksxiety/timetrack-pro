<?php

namespace App\Services;

use App\Models\OvertimeRequest;
use App\Models\RequiredHours;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ReportDataService
{
    public function fetchRawReportData(string $startDate, string $endDate, ?int $orgUnitId): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $weeks = [];
        $current = $start->copy()->startOfWeek(Carbon::SUNDAY);

        while ($current->lessThanOrEqualTo($end)) {
            $weeks[] = [
                'week' => $current->weekOfYear,
                'date' => $current->toDateString(),
            ];
            $current->addWeek();
        }

        $weekNumbers = collect($weeks)->pluck('week');

        $requiredHoursQuery = RequiredHours::select('week', 'required_hours')
            ->whereIn('week', $weekNumbers);

        if ($orgUnitId !== null) {
            $requiredHoursQuery->where('organization_unit_id', $orgUnitId);
        }

        $requiredHours = $requiredHoursQuery->get()
            ->map(function ($item) use ($weeks) {
                $date = collect($weeks)->firstWhere('week', $item->week)['date'] ?? null;
                return [
                    'week' => $item->week,
                    'date' => $date,
                    'required_hours' => (float) $item->required_hours,
                ];
            });

        $requests = OvertimeRequest::with(['schedule.user'])
            ->whereHas('schedule', function ($query) use ($startDate, $endDate, $orgUnitId) {
                $query->whereBetween('date', [$startDate, $endDate])
                    ->whereHas('user', function ($q) use ($orgUnitId) {
                        $q->where('role', 'employee');
                        if ($orgUnitId !== null) {
                            $q->where('organization_unit_id', $orgUnitId);
                        }
                    });
            })
            ->get()
            ->map(function ($req) {
                return [
                    'id' => $req->id,
                    'hours' => (float) $req->hours,
                    'reason' => $req->reason,
                    'status' => $req->status,
                    'date' => $req->schedule->date,
                    'week' => $req->schedule->week,
                    'user_name' => $req->schedule->user->name,
                    'user_id' => $req->schedule->user->employeeid,
                    'user_avatar' => $req->schedule->user->avatar
                        ? Storage::url($req->schedule->user->avatar)
                        : null,
                ];
            });

        return [
            'requests' => $requests,
            'filed' => $requests->where('status', 'FILED'),
            'requiredHours' => $requiredHours,
            'weeks' => $weeks,
        ];
    }

    public function buildStatCards(Collection $requests): array
    {
        return [
            'filed' => round($requests->where('status', 'FILED')->sum('hours'), 2),
            'pending' => $requests->where('status', 'PENDING')->count(),
            'tentative' => round($requests->whereIn('status', ['PENDING', 'APPROVED', 'FILED'])->sum('hours'), 2),
            'requests' => $requests->whereNotIn('status', ['CANCELED', 'DECLINED'])->count(),
        ];
    }

    public function buildHeatmapData(Collection $requests): array
    {
        $activeStatuses = ['APPROVED', 'FILED', 'PENDING'];
        $filtered = $requests->whereIn('status', $activeStatuses);

        $data = $filtered->groupBy('date')
            ->map(fn ($items) => round($items->sum('hours'), 2))
            ->toArray();

        $years = $filtered->pluck('date')
            ->map(fn ($date) => (int) Carbon::parse($date)->year)
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        return [
            'data' => $data,
            'years' => empty($years) ? [(int) now()->year] : $years,
        ];
    }

    public function buildOvertimeTrends(Collection $filed, Collection $requiredHours, array $weeks): array
    {
        return [
            'weekly' => $this->trendsWeekly($filed, $requiredHours),
            'monthly' => $this->trendsMonthly($filed, $requiredHours),
            'yearly' => $this->trendsYearly($filed, $requiredHours),
        ];
    }

    private function trendsWeekly(Collection $filed, Collection $requiredHours): array
    {
        $grouped = $filed->groupBy('week')->sortKeys();

        $labels = [];
        $hours = [];
        $roa = [];

        foreach ($grouped as $week => $items) {
            $labels[] = "Week {$week}";
            $hours[] = round($items->sum('hours'), 2);

            $match = $requiredHours->firstWhere('week', $week);
            $roa[] = $match ? $match['required_hours'] : 0;
        }

        return compact('labels', 'hours', 'roa');
    }

    private function trendsMonthly(Collection $filed, Collection $requiredHours): array
    {
        $grouped = $filed->groupBy(function ($item) {
            return Carbon::parse($item['date'])->format('M Y');
        })->sortKeys();

        $labels = [];
        $hours = [];
        $roa = [];

        foreach ($grouped as $month => $items) {
            $labels[] = $month;
            $hours[] = round($items->sum('hours'), 2);

            $totalReq = $requiredHours->reduce(function ($sum, $rh) use ($month) {
                if (!$rh['date']) return $sum;
                $rhMonth = Carbon::parse($rh['date'])->format('M Y');
                return $rhMonth === $month ? $sum + $rh['required_hours'] : $sum;
            }, 0);

            $roa[] = round($totalReq, 2);
        }

        return compact('labels', 'hours', 'roa');
    }

    private function trendsYearly(Collection $filed, Collection $requiredHours): array
    {
        $grouped = $filed->groupBy(function ($item) {
            return Carbon::parse($item['date'])->year;
        })->sortKeys();

        $labels = [];
        $hours = [];
        $roa = [];

        foreach ($grouped as $year => $items) {
            $labels[] = (string) $year;
            $hours[] = round($items->sum('hours'), 2);

            $totalReq = $requiredHours->reduce(function ($sum, $rh) use ($year) {
                if (!$rh['date']) return $sum;
                $rhYear = (int) Carbon::parse($rh['date'])->year;
                return $rhYear === $year ? $sum + $rh['required_hours'] : $sum;
            }, 0);

            $roa[] = round($totalReq, 2);
        }

        return compact('labels', 'hours', 'roa');
    }

    public function buildEmployeeRankings(Collection $filed): array
    {
        $employees = $filed->groupBy('user_id')
            ->map(function ($items) {
                return [
                    'name' => $items->first()['user_name'],
                    'hours' => round($items->sum('hours'), 2),
                ];
            })
            ->sortByDesc('hours')
            ->values();

        return [
            'names' => $employees->pluck('name')->toArray(),
            'totalHours' => $employees->pluck('hours')->toArray(),
        ];
    }

    public function buildCumulativeOT(Collection $filed): array
    {
        $sorted = $filed->sortBy('date');

        $dates = [];
        $values = [];
        $running = 0;

        foreach ($sorted as $item) {
            $running += $item['hours'];
            $dates[] = $item['date'];
            $values[] = round($running, 2);
        }

        return compact('dates', 'values');
    }

    public function buildGaugeData(Collection $filed, Collection $requiredHours): array
    {
        return [
            'filed_hours' => round($filed->sum('hours'), 2),
            'required_hours' => round($requiredHours->sum('required_hours'), 2),
        ];
    }

    public function buildStatusPie(Collection $requests): array
    {
        $colorMap = [
            'FILED' => '#2563eb',
            'PENDING' => '#f59e0b',
            'APPROVED' => '#570df8',
            'CANCELED' => '#6b7280',
            'DECLINED' => '#ef4444',
            'DISAPPROVED' => '#ef4444',
        ];

        $grouped = $requests->groupBy('status')->sortKeysDesc();

        return [
            'labels' => $grouped->keys()->toArray(),
            'counts' => $grouped->map->count()->values()->toArray(),
            'colors' => $grouped->keys()
                ->map(fn ($s) => $colorMap[$s] ?? '#6b7280')
                ->toArray(),
        ];
    }
}
