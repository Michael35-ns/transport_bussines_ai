<?php

namespace App\Livewire\MaintenanceSchedules;

use App\Enums\MaintenanceIntervalType;
use App\Http\Requests\StoreMaintenanceScheduleRequest;
use App\Models\MaintenanceSchedule;
use App\Models\Truck;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Mantenimientos preventivos')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $truck_id = '';

    public string $task_name = 'Cambio de aceite';

    public string $interval_type = '';

    public ?float $interval_value = null;

    public ?float $last_done_odometer = null;

    public ?float $lead_km = null;

    public bool $active = true;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', MaintenanceSchedule::class);
        $this->resetForm();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, MaintenanceSchedule>
     */
    #[Computed]
    public function schedules(): LengthAwarePaginator
    {
        return MaintenanceSchedule::query()
            ->with('truck')
            ->when($this->search !== '', function ($query): void {
                $term = "%{$this->search}%";
                $query->where('task_name', 'like', $term)
                    ->orWhereHas('truck', fn ($q) => $q->where('plate', 'like', $term));
            })
            ->orderBy('truck_id')
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
     * @return array<string, string>
     */
    #[Computed]
    public function intervalTypeOptions(): array
    {
        return collect(MaintenanceIntervalType::cases())->mapWithKeys(fn (MaintenanceIntervalType $case) => [$case->value => $case->label()])->all();
    }

    public function create(): void
    {
        $this->authorize('create', MaintenanceSchedule::class);

        $this->resetForm();
        Flux::modal('maintenance-schedule-form')->show();
    }

    public function edit(MaintenanceSchedule $schedule): void
    {
        $this->authorize('update', $schedule);

        $this->editingId = $schedule->id;
        $this->truck_id = (string) $schedule->truck_id;
        $this->task_name = $schedule->task_name;
        $this->interval_type = $schedule->interval_type->value;
        $this->interval_value = $schedule->interval_value !== null ? (float) $schedule->interval_value : null;
        $this->last_done_odometer = $schedule->last_done_odometer !== null ? (float) $schedule->last_done_odometer : null;
        $this->lead_km = (float) $schedule->lead_km;
        $this->active = $schedule->active;

        Flux::modal('maintenance-schedule-form')->show();
    }

    public function save(): void
    {
        $schedule = $this->editingId ? MaintenanceSchedule::findOrFail($this->editingId) : null;

        $this->authorize($schedule ? 'update' : 'create', $schedule ?? MaintenanceSchedule::class);

        $validated = $this->validate(StoreMaintenanceScheduleRequest::buildRules());

        if ($schedule) {
            $schedule->update($validated);
        } else {
            MaintenanceSchedule::create($validated);
        }

        unset($this->schedules);
        Flux::modal('maintenance-schedule-form')->close();
        $this->resetForm();

        Flux::toast(variant: 'success', text: $schedule ? __('Programa actualizado.') : __('Programa creado.'));
    }

    public function delete(MaintenanceSchedule $schedule): void
    {
        $this->authorize('delete', $schedule);

        $schedule->delete();

        unset($this->schedules);
        Flux::toast(variant: 'success', text: __('Programa eliminado.'));
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'truck_id', 'interval_value', 'last_done_odometer']);
        $this->task_name = 'Cambio de aceite';
        $this->interval_type = MaintenanceIntervalType::Km->value;
        $this->lead_km = 500.0;
        $this->active = true;
        $this->resetErrorBag();
    }
}
