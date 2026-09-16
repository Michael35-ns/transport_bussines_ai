<?php

namespace App\Livewire\OverheadCosts;

use App\Enums\BillingCycle;
use App\Enums\CostTypeScope;
use App\Http\Requests\StoreOverheadCostRequest;
use App\Models\CostType;
use App\Models\OverheadCost;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Costos generales')]
class Index extends Component
{
    use WithPagination;

    public ?int $editingId = null;

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
        $this->authorize('viewAny', OverheadCost::class);
        $this->resetForm();
    }

    /**
     * @return LengthAwarePaginator<int, OverheadCost>
     */
    #[Computed]
    public function costs(): LengthAwarePaginator
    {
        return OverheadCost::query()
            ->with('costType')
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->paginate(10);
    }

    /**
     * Overhead costs are always classified under the "overhead" cost type
     * scope (salarios, salarios administrativos, cargas sociales, …).
     *
     * @return array<int, string>
     */
    #[Computed]
    public function costTypeOptions(): array
    {
        return CostType::query()->where('scope', CostTypeScope::Overhead)->orderBy('name')->pluck('name', 'id')->all();
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
        $this->authorize('create', OverheadCost::class);

        $this->resetForm();
        Flux::modal('overhead-cost-form')->show();
    }

    public function edit(OverheadCost $cost): void
    {
        $this->authorize('update', $cost);

        $this->editingId = $cost->id;
        $this->cost_type_id = (string) $cost->cost_type_id;
        $this->amount = (float) $cost->amount;
        $this->billing_cycle = $cost->billing_cycle->value;
        $this->effective_from = $cost->effective_from->toDateString();
        $this->effective_to = $cost->effective_to?->toDateString();

        Flux::modal('overhead-cost-form')->show();
    }

    public function save(): void
    {
        $cost = $this->editingId ? OverheadCost::findOrFail($this->editingId) : null;

        $this->authorize($cost ? 'update' : 'create', $cost ?? OverheadCost::class);

        $validated = $this->validate(StoreOverheadCostRequest::buildRules());

        if ($cost) {
            $cost->update($validated);
        } else {
            OverheadCost::create($validated);
        }

        unset($this->costs);
        Flux::modal('overhead-cost-form')->close();
        $this->resetForm();

        Flux::toast(variant: 'success', text: $cost ? __('Costo actualizado.') : __('Costo creado.'));
    }

    public function delete(OverheadCost $cost): void
    {
        $this->authorize('delete', $cost);

        $cost->delete();

        unset($this->costs);
        Flux::toast(variant: 'success', text: __('Costo eliminado.'));
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'cost_type_id', 'amount', 'effective_to']);
        $this->billing_cycle = BillingCycle::Monthly->value;
        $this->effective_from = now()->toDateString();
        $this->resetErrorBag();
    }
}
