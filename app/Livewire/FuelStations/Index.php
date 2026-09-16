<?php

namespace App\Livewire\FuelStations;

use App\Http\Requests\StoreFuelStationRequest;
use App\Models\FuelStation;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Estaciones de combustible')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $name = '';

    public ?string $location = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', FuelStation::class);
        $this->resetForm();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, FuelStation>
     */
    #[Computed]
    public function stations(): LengthAwarePaginator
    {
        return FuelStation::query()
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(10);
    }

    public function create(): void
    {
        $this->authorize('create', FuelStation::class);

        $this->resetForm();
        Flux::modal('fuel-station-form')->show();
    }

    public function edit(FuelStation $station): void
    {
        $this->authorize('update', $station);

        $this->editingId = $station->id;
        $this->name = $station->name;
        $this->location = $station->location;

        Flux::modal('fuel-station-form')->show();
    }

    public function save(): void
    {
        $station = $this->editingId ? FuelStation::findOrFail($this->editingId) : null;

        $this->authorize($station ? 'update' : 'create', $station ?? FuelStation::class);

        $validated = $this->validate((new StoreFuelStationRequest)->rules());

        if ($station) {
            $station->update($validated);
        } else {
            FuelStation::create($validated);
        }

        unset($this->stations);
        Flux::modal('fuel-station-form')->close();
        $this->resetForm();

        Flux::toast(variant: 'success', text: $station ? __('Estación actualizada.') : __('Estación creada.'));
    }

    public function delete(FuelStation $station): void
    {
        $this->authorize('delete', $station);

        $station->delete();

        unset($this->stations);
        Flux::toast(variant: 'success', text: __('Estación eliminada.'));
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'location']);
        $this->resetErrorBag();
    }
}
