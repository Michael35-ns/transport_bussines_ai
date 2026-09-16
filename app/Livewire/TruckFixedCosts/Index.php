<?php

namespace App\Livewire\TruckFixedCosts;

use App\Enums\BillingCycle;
use App\Enums\CostTypeScope;
use App\Http\Requests\StoreTruckFixedCostRequest;
use App\Models\CostType;
use App\Models\Truck;
use App\Models\TruckFixedCost;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Costos fijos por camión')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $truck_id = '';

    public string $cost_type_id = '';

    public ?float $amount = null;

    public string $billing_cycle = '';

    public ?string $effective_from = null;

    public ?string $effective_to = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', TruckFixedCost::class);
        $this->resetForm();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, TruckFixedCost>
     */
    #[Computed]
    public function costs(): LengthAwarePaginator
    {
        return TruckFixedCost::query()
            ->with(['truck', 'costType'])
            ->when($this->search !== '', fn ($query) => $query->whereHas('truck', fn ($q) => $q->where('plate', 'like', "%{$this->search}%")))
            ->orderByDesc('effective_from')
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
     * Truck-fixed costs are always classified under the "truck_fixed" cost
     * type scope (seguro, permisos, fumigación, dekra, marchamo).
     *
     * @return array<int, string>
     */
    #[Computed]
    public function costTypeOptions(): array
    {
        return CostType::query()->where('scope', CostTypeScope::TruckFixed)->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function billingCycleOptions(): array
    {
        return collect(BillingCycle::cases())->mapWithKeys(fn (BillingCycle $case) => [$case->value => $case->label()])->all();
    }

    public function create(): void
    {
        $this->authorize('create', TruckFixedCost::class);

        $this->resetForm();
        Flux::modal('truck-fixed-cost-form')->show();
    }

    public function edit(TruckFixedCost $cost): void
    {
        $this->authorize('update', $cost);

        $this->editingId = $cost->id;
        $this->truck_id = (string) $cost->truck_id;
        $this->cost_type_id = (string) $cost->cost_type_id;
        $this->amount = (float) $cost->amount;
        $this->billing_cycle = $cost->billing_cycle->value;
        $this->effective_from = $cost->effective_from->toDateString();
        $this->effective_to = $cost->effective_to?->toDateString();

        Flux::modal('truck-fixed-cost-form')->show();
    }

    public function save(): void
    {
        $cost = $this->editingId ? TruckFixedCost::findOrFail($this->editingId) : null;

        $this->authorize($cost ? 'update' : 'create', $cost ?? TruckFixedCost::class);

        $validated = $this->validate(StoreTruckFixedCostRequest::buildRules());

        if ($cost) {
            $cost->update($validated);
        } else {
            TruckFixedCost::create($validated);
        }

        unset($this->costs);
        Flux::modal('truck-fixed-cost-form')->close();
        $this->resetForm();

        Flux::toast(variant: 'success', text: $cost ? __('Costo actualizado.') : __('Costo creado.'));
    }

    public function delete(TruckFixedCost $cost): void
    {
        $this->authorize('delete', $cost);

        $cost->delete();

        unset($this->costs);
        Flux::toast(variant: 'success', text: __('Costo eliminado.'));
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'truck_id', 'cost_type_id', 'amount', 'effective_to']);
        $this->billing_cycle = BillingCycle::Monthly->value;
        $this->effective_from = now()->toDateString();
        $this->resetErrorBag();
    }
}
