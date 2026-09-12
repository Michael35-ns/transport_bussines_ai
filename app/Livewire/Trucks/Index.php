<?php

namespace App\Livewire\Trucks;

use App\Enums\AcquisitionMode;
use App\Enums\ActiveStatus;
use App\Enums\VehicleType;
use App\Http\Requests\StoreTruckRequest;
use App\Models\Truck;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Camiones')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $plate = '';

    public ?string $internal_no = null;

    public string $vehicle_type = '';

    public ?string $make = null;

    public ?string $model = null;

    public ?int $year = null;

    public ?string $acquisition_date = null;

    public string $acquisition_mode = '';

    public ?float $financing_monthly = null;

    public ?float $current_odometer = null;

    public string $status = '';

    public ?string $base_yard = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', Truck::class);
        $this->resetForm();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, Truck>
     */
    #[Computed]
    public function trucks(): LengthAwarePaginator
    {
        return Truck::query()
            ->when($this->search !== '', function ($query): void {
                $query->where('plate', 'like', "%{$this->search}%")
                    ->orWhere('internal_no', 'like', "%{$this->search}%");
            })
            ->orderBy('plate')
            ->paginate(10);
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function vehicleTypeOptions(): array
    {
        return collect(VehicleType::cases())->mapWithKeys(fn (VehicleType $case) => [$case->value => $case->label()])->all();
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function acquisitionModeOptions(): array
    {
        return collect(AcquisitionMode::cases())->mapWithKeys(fn (AcquisitionMode $case) => [$case->value => $case->label()])->all();
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function statusOptions(): array
    {
        return collect(ActiveStatus::cases())->mapWithKeys(fn (ActiveStatus $case) => [$case->value => $case->label()])->all();
    }

    /**
     * Open the form to create a new truck.
     */
    public function create(): void
    {
        $this->authorize('create', Truck::class);

        $this->resetForm();
        Flux::modal('truck-form')->show();
    }

    /**
     * Open the form to edit an existing truck.
     */
    public function edit(Truck $truck): void
    {
        $this->authorize('update', $truck);

        $this->editingId = $truck->id;
        $this->plate = $truck->plate;
        $this->internal_no = $truck->internal_no;
        $this->vehicle_type = $truck->vehicle_type->value;
        $this->make = $truck->make;
        $this->model = $truck->model;
        $this->year = $truck->year;
        $this->acquisition_date = $truck->acquisition_date?->toDateString();
        $this->acquisition_mode = $truck->acquisition_mode->value;
        $this->financing_monthly = $truck->financing_monthly !== null ? (float) $truck->financing_monthly : null;
        $this->status = $truck->status->value;
        $this->base_yard = $truck->base_yard;

        Flux::modal('truck-form')->show();
    }

    /**
     * Persist the truck being created or edited.
     */
    public function save(): void
    {
        $truck = $this->editingId ? Truck::findOrFail($this->editingId) : null;

        $this->authorize($truck ? 'update' : 'create', $truck ?? Truck::class);

        $validated = $this->validate(StoreTruckRequest::buildRules(
            ignoring: $truck,
            includeOdometer: $truck === null,
        ));

        if ($truck) {
            $truck->update($validated);
        } else {
            // The column is not-null with a default of 0 — an explicit null
            // here would override that default and fail the insert.
            $validated['current_odometer'] ??= 0;
            Truck::create($validated);
        }

        unset($this->trucks);
        Flux::modal('truck-form')->close();
        $this->resetForm();

        Flux::toast(variant: 'success', text: $truck ? __('Camión actualizado.') : __('Camión creado.'));
    }

    /**
     * Delete a truck.
     */
    public function delete(Truck $truck): void
    {
        $this->authorize('delete', $truck);

        $truck->delete();

        unset($this->trucks);
        Flux::toast(variant: 'success', text: __('Camión eliminado.'));
    }

    /**
     * Reset the create/edit form back to its defaults.
     */
    public function resetForm(): void
    {
        $this->reset([
            'editingId', 'plate', 'internal_no', 'vehicle_type', 'make', 'model',
            'year', 'acquisition_date', 'financing_monthly', 'current_odometer', 'base_yard',
        ]);
        $this->acquisition_mode = AcquisitionMode::Owned->value;
        $this->status = ActiveStatus::Active->value;
        $this->resetErrorBag();
    }
}
