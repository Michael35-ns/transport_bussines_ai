<?php

namespace App\Livewire\Dashboard;

use App\Models\MaintenanceSchedule;
use App\Services\Financial\FinancialCalculator;
use App\Services\Financial\Period;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * The weekly KPI dashboard from docs/finance/financial-model.md: fleet
 * revenue/cost/profit/margin, cost & revenue per km, availability &
 * utilization, and preventive-maintenance alerts. Every per-km figure is an
 * estimate while trip distance still defaults from route.standard_km
 * (docs/decisions/0002-trip-distance-source.md) — labelled as such, never
 * presented as exact.
 *
 * Read-only: every authenticated role may view it, matching ModelPolicy's
 * "everyone reads" rule for the rest of the app's data.
 *
 * @phpstan-import-type FleetRow from FinancialCalculator
 *
 * @property-read list<FleetRow> $fleetRows Livewire's #[Computed] magic property — see fleetRows() below.
 */
#[Title('Dashboard')]
class Index extends Component
{
    public string $anchorDate = '';

    public function mount(): void
    {
        $this->anchorDate = now()->toDateString();
    }

    public function previousWeek(): void
    {
        $this->anchorDate = Carbon::parse($this->anchorDate)->subWeek()->toDateString();
    }

    public function nextWeek(): void
    {
        $this->anchorDate = Carbon::parse($this->anchorDate)->addWeek()->toDateString();
    }

    public function thisWeek(): void
    {
        $this->anchorDate = now()->toDateString();
    }

    /**
     * @return list<FleetRow>
     */
    #[Computed]
    public function fleetRows(): array
    {
        return app(FinancialCalculator::class)->fleetSummary($this->period());
    }

    /**
     * @return array{
     *     revenue: float, total_cost: float, profit_net: float, km: float,
     *     margin_pct: ?float, cost_per_km: ?float, revenue_per_km: ?float,
     * }
     */
    #[Computed]
    public function fleetTotals(): array
    {
        $rows = $this->fleetRows;

        $revenue = round(array_sum(array_column($rows, 'revenue')), 2);
        $totalCost = round(array_sum(array_column($rows, 'total_cost')), 2);
        $profitNet = round(array_sum(array_column($rows, 'profit_net')), 2);
        $km = round(array_sum(array_column($rows, 'km')), 2);

        return [
            'revenue' => $revenue,
            'total_cost' => $totalCost,
            'profit_net' => $profitNet,
            'km' => $km,
            'margin_pct' => $revenue > 0 ? round($profitNet / $revenue * 100, 2) : null,
            'cost_per_km' => $km > 0 ? round($totalCost / $km, 2) : null,
            'revenue_per_km' => $km > 0 ? round($revenue / $km, 2) : null,
        ];
    }

    /**
     * Count of active preventive schedules currently VENCIDO/PROXIMO
     * (docs/business/discovery.md §I "Maintenance status").
     *
     * @return array{overdue: int, upcoming: int}
     */
    #[Computed]
    public function maintenanceAlerts(): array
    {
        $statuses = MaintenanceSchedule::query()
            ->where('active', true)
            ->with('truck')
            ->get()
            ->map(fn (MaintenanceSchedule $schedule) => $schedule->status());

        return [
            'overdue' => $statuses->filter(fn (string $status) => $status === 'VENCIDO')->count(),
            'upcoming' => $statuses->filter(fn (string $status) => $status === 'PROXIMO')->count(),
        ];
    }

    public function period(): Period
    {
        return Period::weekContaining(Carbon::parse($this->anchorDate));
    }
}
