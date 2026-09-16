<?php

namespace App\Livewire\FuelRecords;

use App\Enums\PaymentMethod;
use App\Http\Requests\StoreFuelRecordRequest;
use App\Models\FuelRecord;
use App\Models\FuelStation;
use App\Models\Trip;
use App\Models\Truck;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Combustible')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $truck_id = '';

    public string $trip_id = '';

    public string $fuel_station_id = '';

    public ?string $occurred_at = null;

    public ?float $liters = null;

    public ?float $unit_price = null;

    public string $payment_method = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', FuelRecord::class);
        $this->resetForm();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, FuelRecord>
     */
    #[Computed]
    public function records(): LengthAwarePaginator
    {
        return FuelRecord::query()
            ->with(['truck', 'fuelStation'])
            ->when($this->search !== '', fn ($query) => $query->whereHas('truck', fn ($q) => $q->where('plate', 'like', "%{$this->search}%")))
            ->orderByDesc('occurred_at')
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
    public function fuelStationOptions(): array
    {
        return FuelStation::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * The selected truck's trips, to optionally attribute the fuel purchase
     * to one of them.
     *
     * @return array<int, string>
     */
    #[Computed]
    public function tripOptions(): array
    {
        if ($this->truck_id === '') {
            return [];
        }

        return Trip::query()
            ->where('truck_id', $this->truck_id)
            ->with('route')
            ->orderByDesc('planned_start')
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (Trip $trip) => [$trip->id => $trip->route->name.' — '.($trip->planned_start?->format('d/m/Y') ?? __('sin fecha'))])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function paymentMethodOptions(): array
    {
        return collect(PaymentMethod::cases())->mapWithKeys(fn (PaymentMethod $case) => [$case->value => $case->label()])->all();
    }

    #[Computed]
    public function totalPreview(): float
    {
        return round(($this->liters ?? 0) * ($this->unit_price ?? 0), 2);
    }

    public function updatedTruckId(): void
    {
        $this->trip_id = '';
    }

    public function create(): void
    {
        $this->authorize('create', FuelRecord::class);

        $this->resetForm();
        Flux::modal('fuel-record-form')->show();
    }

    public function edit(FuelRecord $record): void
    {
        $this->authorize('update', $record);

        $this->editingId = $record->id;
        $this->truck_id = (string) $record->truck_id;
        $this->trip_id = $record->trip_id !== null ? (string) $record->trip_id : '';
        $this->fuel_station_id = (string) $record->fuel_station_id;
        $this->occurred_at = $record->occurred_at->format('Y-m-d\TH:i');
        $this->liters = (float) $record->liters;
        $this->unit_price = (float) $record->unit_price;
        $this->payment_method = $record->payment_method->value;

        Flux::modal('fuel-record-form')->show();
    }

    public function save(): void
    {
        $record = $this->editingId ? FuelRecord::findOrFail($this->editingId) : null;

        $this->authorize($record ? 'update' : 'create', $record ?? FuelRecord::class);

        // Built explicitly (not $this->validate()) so an unselected trip_id
        // ('' from the <select>) normalizes to null before the
        // nullable+integer rule sees it.
        $validated = Validator::make([
            'truck_id' => $this->truck_id,
            'trip_id' => $this->trip_id !== '' ? $this->trip_id : null,
            'fuel_station_id' => $this->fuel_station_id,
            'occurred_at' => $this->occurred_at,
            'liters' => $this->liters,
            'unit_price' => $this->unit_price,
            'payment_method' => $this->payment_method,
        ], StoreFuelRecordRequest::buildRules())->validate();

        $validated['total'] = round($validated['liters'] * $validated['unit_price'], 2);

        if ($record) {
            $record->update($validated);
        } else {
            // created_by is intentionally not mass-assignable — set it directly.
            $record = new FuelRecord($validated);
            $record->created_by = auth()->user()->id;
            $record->save();
        }

        unset($this->records);
        Flux::modal('fuel-record-form')->close();
        $this->resetForm();

        Flux::toast(variant: 'success', text: $record->wasRecentlyCreated ? __('Registro de combustible creado.') : __('Registro actualizado.'));
    }

    public function delete(FuelRecord $record): void
    {
        $this->authorize('delete', $record);

        $record->delete();

        unset($this->records);
        Flux::toast(variant: 'success', text: __('Registro eliminado.'));
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'truck_id', 'trip_id', 'fuel_station_id', 'liters', 'unit_price']);
        $this->occurred_at = now()->format('Y-m-d\TH:i');
        $this->payment_method = PaymentMethod::Cash->value;
        $this->resetErrorBag();
    }
}
