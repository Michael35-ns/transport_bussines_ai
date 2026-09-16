<?php

namespace App\Livewire\Maintenances;

use App\Enums\MaintenanceType;
use App\Http\Requests\StoreMaintenanceRequest;
use App\Models\CostType;
use App\Models\Maintenance;
use App\Models\MaintenanceProvider;
use App\Models\MaintenanceSchedule;
use App\Models\Truck;
use App\Services\Maintenance\OdometerService;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Mantenimientos')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $truck_id = '';

    public string $type = '';

    public string $cost_type_id = '';

    public string $provider_id = '';

    public string $schedule_id = '';

    public ?string $entry_date = null;

    public ?string $completion_date = null;

    public ?float $odometer = null;

    public ?string $description = null;

    public float $parts_cost = 0.0;

    public float $labor_cost = 0.0;

    public float $other_cost = 0.0;

    public float $downtime_days = 0.0;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', Maintenance::class);
        $this->resetForm();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, Maintenance>
     */
    #[Computed]
    public function maintenances(): LengthAwarePaginator
    {
        return Maintenance::query()
            ->with(['truck', 'provider'])
            ->when($this->search !== '', fn ($query) => $query->whereHas('truck', fn ($q) => $q->where('plate', 'like', "%{$this->search}%")))
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate(10);
    }

    /**
     * @return array<int, string>
     */
    #[Computed]
    public function truckOptions(): array
    {
        return Truck::query()->orderBy('plate')->pluck('plate', 'id')->all();
    }

    /**
     * @return array<int, string>
     */
    #[Computed]
    public function providerOptions(): array
    {
        return MaintenanceProvider::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * @return array<int, string>
     */
    #[Computed]
    public function costTypeOptions(): array
    {
        return CostType::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * The selected truck's active preventive schedules — only relevant when
     * this service closes one out (docs/decisions/0002).
     *
     * @return array<int, string>
     */
    #[Computed]
    public function scheduleOptions(): array
    {
        if ($this->truck_id === '') {
            return [];
        }

        return MaintenanceSchedule::query()
            ->where('truck_id', $this->truck_id)
            ->where('active', true)
            ->pluck('task_name', 'id')
            ->all();
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function typeOptions(): array
    {
        return collect(MaintenanceType::cases())->mapWithKeys(fn (MaintenanceType $case) => [$case->value => $case->label()])->all();
    }

    #[Computed]
    public function totalPreview(): float
    {
        return round($this->parts_cost + $this->labor_cost + $this->other_cost, 2);
    }

    /**
     * Selecting a different truck invalidates any previously chosen
     * schedule, since schedules belong to one truck.
     */
    public function updatedTruckId(): void
    {
        $this->schedule_id = '';
    }

    public function create(): void
    {
        $this->authorize('create', Maintenance::class);

        $this->resetForm();
        Flux::modal('maintenance-form')->show();
    }

    public function edit(Maintenance $maintenance): void
    {
        $this->authorize('update', $maintenance);

        $this->editingId = $maintenance->id;
        $this->truck_id = (string) $maintenance->truck_id;
        $this->type = $maintenance->type->value;
        $this->cost_type_id = $maintenance->cost_type_id !== null ? (string) $maintenance->cost_type_id : '';
        $this->provider_id = (string) $maintenance->provider_id;
        $this->schedule_id = $maintenance->schedule_id !== null ? (string) $maintenance->schedule_id : '';
        $this->entry_date = $maintenance->entry_date->toDateString();
        $this->completion_date = $maintenance->completion_date?->toDateString();
        $this->odometer = $maintenance->odometer !== null ? (float) $maintenance->odometer : null;
        $this->description = $maintenance->description;
        $this->parts_cost = (float) $maintenance->parts_cost;
        $this->labor_cost = (float) $maintenance->labor_cost;
        $this->other_cost = (float) $maintenance->other_cost;
        $this->downtime_days = (float) $maintenance->downtime_days;

        Flux::modal('maintenance-form')->show();
    }

    public function save(): void
    {
        $maintenance = $this->editingId ? Maintenance::findOrFail($this->editingId) : null;

        $this->authorize($maintenance ? 'update' : 'create', $maintenance ?? Maintenance::class);

        // Built explicitly (not $this->validate()) so unselected
        // cost_type_id/schedule_id ('' from their <select>s) normalize to
        // null before the nullable+integer rules see them.
        $validated = Validator::make([
            'truck_id' => $this->truck_id,
            'type' => $this->type,
            'cost_type_id' => $this->cost_type_id !== '' ? $this->cost_type_id : null,
            'provider_id' => $this->provider_id,
            'schedule_id' => $this->schedule_id !== '' ? $this->schedule_id : null,
            'entry_date' => $this->entry_date,
            'completion_date' => $this->completion_date,
            'odometer' => $this->odometer,
            'description' => $this->description,
            'parts_cost' => $this->parts_cost,
            'labor_cost' => $this->labor_cost,
            'other_cost' => $this->other_cost,
            'downtime_days' => $this->downtime_days,
        ], StoreMaintenanceRequest::buildRules())->validate();

        $validated['total'] = round($validated['parts_cost'] + $validated['labor_cost'] + $validated['other_cost'], 2);

        if ($maintenance) {
            $maintenance->update($validated);
        } else {
            // created_by is intentionally not mass-assignable — set it directly.
            $maintenance = new Maintenance($validated);
            $maintenance->created_by = auth()->user()->id;
            $maintenance->save();
        }

        app(OdometerService::class)->applyReading($maintenance->fresh(['truck']));

        unset($this->maintenances);
        Flux::modal('maintenance-form')->close();
        $this->resetForm();

        Flux::toast(variant: 'success', text: $maintenance->wasRecentlyCreated ? __('Mantenimiento creado.') : __('Mantenimiento actualizado.'));
    }

    public function delete(Maintenance $maintenance): void
    {
        $this->authorize('delete', $maintenance);

        $maintenance->delete();

        unset($this->maintenances);
        Flux::toast(variant: 'success', text: __('Mantenimiento eliminado.'));
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId', 'truck_id', 'cost_type_id', 'schedule_id', 'completion_date',
            'odometer', 'description',
        ]);
        $this->type = MaintenanceType::Preventive->value;
        $this->provider_id = '';
        $this->entry_date = now()->toDateString();
        $this->parts_cost = 0.0;
        $this->labor_cost = 0.0;
        $this->other_cost = 0.0;
        $this->downtime_days = 0.0;
        $this->resetErrorBag();
    }
}
