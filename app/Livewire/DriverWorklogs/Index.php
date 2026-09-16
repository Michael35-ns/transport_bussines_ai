<?php

namespace App\Livewire\DriverWorklogs;

use App\Http\Requests\StoreDriverWorklogRequest;
use App\Models\Driver;
use App\Models\DriverWorklog;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Horas de conductores')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $driver_id = '';

    public ?string $work_date = null;

    public ?float $hours = null;

    public ?string $notes = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', DriverWorklog::class);
        $this->resetForm();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, DriverWorklog>
     */
    #[Computed]
    public function worklogs(): LengthAwarePaginator
    {
        return DriverWorklog::query()
            ->with('driver')
            ->when($this->search !== '', fn ($query) => $query->whereHas('driver', fn ($q) => $q->where('name', 'like', "%{$this->search}%")))
            ->orderByDesc('work_date')
            ->orderByDesc('id')
            ->paginate(10);
    }

    /**
     * @return array<int, string>
     */
    #[Computed]
    public function driverOptions(): array
    {
        return Driver::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * A live preview of the pay this worklog will compute to, using the
     * selected driver's current hourly_rate — the actual snapshot is only
     * taken on save (docs/decisions/0001).
     */
    #[Computed]
    public function payPreview(): ?float
    {
        if ($this->driver_id === '' || $this->hours === null) {
            return null;
        }

        $driver = Driver::find($this->driver_id);

        return $driver ? round($this->hours * (float) $driver->hourly_rate, 2) : null;
    }

    public function create(): void
    {
        $this->authorize('create', DriverWorklog::class);

        $this->resetForm();
        Flux::modal('driver-worklog-form')->show();
    }

    public function edit(DriverWorklog $worklog): void
    {
        $this->authorize('update', $worklog);

        $this->editingId = $worklog->id;
        $this->driver_id = (string) $worklog->driver_id;
        $this->work_date = $worklog->work_date->toDateString();
        $this->hours = (float) $worklog->hours;
        $this->notes = $worklog->notes;

        Flux::modal('driver-worklog-form')->show();
    }

    public function save(): void
    {
        $worklog = $this->editingId ? DriverWorklog::findOrFail($this->editingId) : null;

        $this->authorize($worklog ? 'update' : 'create', $worklog ?? DriverWorklog::class);

        $validated = $this->validate(StoreDriverWorklogRequest::buildRules($worklog, $this->driver_id));

        $driver = Driver::findOrFail((int) $validated['driver_id']);
        $validated['hourly_rate_snapshot'] = (float) $driver->hourly_rate;
        $validated['computed_pay'] = round($validated['hours'] * $validated['hourly_rate_snapshot'], 2);

        if ($worklog) {
            $worklog->update($validated);
        } else {
            // created_by is intentionally not mass-assignable — set it directly.
            $worklog = new DriverWorklog($validated);
            $worklog->created_by = auth()->user()->id;
            $worklog->save();
        }

        unset($this->worklogs);
        Flux::modal('driver-worklog-form')->close();
        $this->resetForm();

        Flux::toast(variant: 'success', text: $worklog->wasRecentlyCreated ? __('Jornada registrada.') : __('Jornada actualizada.'));
    }

    public function delete(DriverWorklog $worklog): void
    {
        $this->authorize('delete', $worklog);

        $worklog->delete();

        unset($this->worklogs);
        Flux::toast(variant: 'success', text: __('Jornada eliminada.'));
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'driver_id', 'hours', 'notes']);
        $this->work_date = now()->toDateString();
        $this->resetErrorBag();
    }
}
