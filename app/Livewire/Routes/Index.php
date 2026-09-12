<?php

namespace App\Livewire\Routes;

use App\Http\Requests\StoreRouteRequest;
use App\Models\Route;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Rutas')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $name = '';

    public string $origin = '';

    public string $destination = '';

    public ?float $standard_km = null;

    public ?float $typical_toll_cost = null;

    public bool $is_round_trip = false;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', Route::class);
        $this->resetForm();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, Route>
     */
    #[Computed]
    public function routes(): LengthAwarePaginator
    {
        return Route::query()
            ->when($this->search !== '', function ($query): void {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('origin', 'like', "%{$this->search}%")
                    ->orWhere('destination', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->paginate(10);
    }

    public function create(): void
    {
        $this->authorize('create', Route::class);

        $this->resetForm();
        Flux::modal('route-form')->show();
    }

    public function edit(Route $route): void
    {
        $this->authorize('update', $route);

        $this->editingId = $route->id;
        $this->name = $route->name;
        $this->origin = $route->origin;
        $this->destination = $route->destination;
        $this->standard_km = (float) $route->standard_km;
        $this->typical_toll_cost = $route->typical_toll_cost !== null ? (float) $route->typical_toll_cost : null;
        $this->is_round_trip = $route->is_round_trip;

        Flux::modal('route-form')->show();
    }

    public function save(): void
    {
        $route = $this->editingId ? Route::findOrFail($this->editingId) : null;

        $this->authorize($route ? 'update' : 'create', $route ?? Route::class);

        $validated = $this->validate((new StoreRouteRequest)->rules());
        $validated['is_round_trip'] ??= false;

        if ($route) {
            $route->update($validated);
        } else {
            Route::create($validated);
        }

        unset($this->routes);
        Flux::modal('route-form')->close();
        $this->resetForm();

        Flux::toast(variant: 'success', text: $route ? __('Ruta actualizada.') : __('Ruta creada.'));
    }

    public function delete(Route $route): void
    {
        $this->authorize('delete', $route);

        $route->delete();

        unset($this->routes);
        Flux::toast(variant: 'success', text: __('Ruta eliminada.'));
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'origin', 'destination', 'standard_km', 'typical_toll_cost', 'is_round_trip']);
        $this->resetErrorBag();
    }
}
