<?php

namespace App\Livewire\Trips;

use App\Enums\TripStatus;
use App\Http\Requests\StoreTripRequest;
use App\Models\Driver;
use App\Models\RateAgreement;
use App\Models\Route;
use App\Models\Trip;
use App\Models\Truck;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Viajes')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $truck_id = '';

    public string $driver_id = '';

    public string $route_id = '';

    public string $rate_agreement_id = '';

    public ?float $distance = null;

    public bool $distance_estimated = true;

    public ?float $price = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', Trip::class);
        $this->resetForm();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, Trip>
     */
    #[Computed]
    public function trips(): LengthAwarePaginator
    {
        return Trip::query()
            ->with(['truck', 'driver', 'route'])
            ->when($this->search !== '', function ($query): void {
                $term = "%{$this->search}%";
                $query->whereHas('truck', fn ($q) => $q->where('plate', 'like', $term))
                    ->orWhereHas('driver', fn ($q) => $q->where('name', 'like', $term))
                    ->orWhereHas('route', fn ($q) => $q->where('name', 'like', $term));
            })
            ->orderByDesc('actual_end')
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
    public function driverOptions(): array
    {
        return Driver::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * @return array<int, string>
     */
    #[Computed]
    public function routeOptions(): array
    {
        return Route::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * Rate agreements for the currently selected route (or customer-wide
     * agreements with no route), used only to prefill the price/customer —
     * trips.price stays authoritative and editable after selection.
     *
     * @return array<int, string>
     */
    #[Computed]
    public function rateAgreementOptions(): array
    {
        if ($this->route_id === '') {
            return [];
        }

        return RateAgreement::query()
            ->with('customer')
            ->where('active', true)
            ->where(fn ($q) => $q->where('route_id', $this->route_id)->orWhereNull('route_id'))
            ->get()
            ->mapWithKeys(fn (RateAgreement $agreement) => [
                $agreement->id => "{$agreement->customer->name} — ₡".number_format((float) $agreement->price, 2),
            ])
            ->all();
    }

    /**
     * Selecting a route defaults the trip's distance to its standard_km
     * (docs/decisions/0002) and clears any rate agreement tied to the
     * previous route.
     */
    public function updatedRouteId(): void
    {
        $this->rate_agreement_id = '';

        $route = $this->route_id !== '' ? Route::find($this->route_id) : null;

        if ($route !== null) {
            $this->distance = (float) $route->standard_km;
            $this->distance_estimated = true;
        }
    }

    /**
     * A manual edit to the distance means it's no longer the route's
     * default — mark it as a real, non-estimated value.
     */
    public function updatedDistance(): void
    {
        $this->distance_estimated = false;
    }

    /**
     * Selecting a rate agreement defaults the price to its negotiated rate;
     * the field stays editable since trips.price is always authoritative.
     */
    public function updatedRateAgreementId(): void
    {
        $agreement = $this->rate_agreement_id !== '' ? RateAgreement::find($this->rate_agreement_id) : null;

        if ($agreement !== null) {
            $this->price = (float) $agreement->price;
        }
    }

    /**
     * Open the form to create a new trip.
     */
    public function create(): void
    {
        $this->authorize('create', Trip::class);

        $this->resetForm();
        Flux::modal('trip-form')->show();
    }

    /**
     * Open the form to edit an existing trip.
     */
    public function edit(Trip $trip): void
    {
        $this->authorize('update', $trip);

        $this->editingId = $trip->id;
        $this->truck_id = (string) $trip->truck_id;
        $this->driver_id = (string) $trip->driver_id;
        $this->route_id = (string) $trip->route_id;
        $this->rate_agreement_id = $trip->rate_agreement_id !== null ? (string) $trip->rate_agreement_id : '';
        $this->distance = $trip->distance !== null ? (float) $trip->distance : null;
        $this->distance_estimated = $trip->distance_estimated;
        $this->price = $trip->price !== null ? (float) $trip->price : null;

        Flux::modal('trip-form')->show();
    }

    /**
     * Persist the trip being created or edited.
     */
    public function save(): void
    {
        $trip = $this->editingId ? Trip::findOrFail($this->editingId) : null;

        $this->authorize($trip ? 'update' : 'create', $trip ?? Trip::class);

        // Built explicitly (not $this->validate()) so an unselected
        // rate_agreement_id ('' from the select) can normalize to null
        // before the "nullable"+"integer" rule sees it.
        $validated = Validator::make([
            'truck_id' => $this->truck_id,
            'driver_id' => $this->driver_id,
            'route_id' => $this->route_id,
            'rate_agreement_id' => $this->rate_agreement_id !== '' ? $this->rate_agreement_id : null,
            'distance' => $this->distance,
            'distance_estimated' => $this->distance_estimated,
            'price' => $this->price,
        ], StoreTripRequest::buildRules())->validate();

        if ($trip) {
            $trip->update($validated);
        } else {
            // Every trip is captured after the fact — never planned ahead in
            // this UI — so it's always completed as of right now, rather
            // than asking for a status and a completion date.
            $validated['status'] = TripStatus::Completed;
            $validated['actual_end'] = now();

            // created_by is intentionally not mass-assignable — set it directly.
            $trip = new Trip($validated);
            $trip->created_by = auth()->user()->id;
            $trip->save();
        }

        unset($this->trips);
        Flux::modal('trip-form')->close();
        $this->resetForm();

        Flux::toast(variant: 'success', text: $trip->wasRecentlyCreated ? __('Viaje creado.') : __('Viaje actualizado.'));
    }

    /**
     * Delete a trip.
     */
    public function delete(Trip $trip): void
    {
        $this->authorize('delete', $trip);

        $trip->delete();

        unset($this->trips);
        Flux::toast(variant: 'success', text: __('Viaje eliminado.'));
    }

    /**
     * Reset the create/edit form back to its defaults.
     */
    public function resetForm(): void
    {
        $this->reset(['editingId', 'truck_id', 'driver_id', 'route_id', 'rate_agreement_id', 'distance', 'price']);
        $this->distance_estimated = true;
        $this->resetErrorBag();
    }
}
