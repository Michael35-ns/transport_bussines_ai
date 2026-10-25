<?php

namespace App\Livewire\CostTypes;

use App\Http\Requests\StoreCostTypeRequest;
use App\Models\CostType;
use App\Models\Maintenance;
use App\Models\OverheadCost;
use App\Models\TripExpense;
use App\Models\TruckExpense;
use App\Models\TruckFixedCost;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Categorías de costos')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $name = '';

    public string $scope = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', CostType::class);
        $this->resetForm();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, CostType>
     */
    #[Computed]
    public function costTypes(): LengthAwarePaginator
    {
        return CostType::query()
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->orderBy('scope')
            ->orderBy('name')
            ->paginate(15);
    }

    public function create(): void
    {
        $this->authorize('create', CostType::class);

        $this->resetForm();
        Flux::modal('cost-type-form')->show();
    }

    public function edit(CostType $costType): void
    {
        $this->authorize('update', $costType);

        $this->editingId = $costType->id;
        $this->name = $costType->name;
        $this->scope = $costType->scope->value;

        Flux::modal('cost-type-form')->show();
    }

    public function save(): void
    {
        $costType = $this->editingId ? CostType::findOrFail($this->editingId) : null;

        $this->authorize($costType ? 'update' : 'create', $costType ?? CostType::class);

        $validated = $this->validate(StoreCostTypeRequest::buildRules($costType));

        // Moving a category to another scope would make it vanish from the
        // dropdown its existing records were entered through.
        if ($costType && $costType->scope->value !== $validated['scope'] && $this->isInUse($costType)) {
            $this->addError('scope', __('No se puede cambiar el tipo: la categoría ya tiene registros.'));

            return;
        }

        if ($costType) {
            $costType->update($validated);
        } else {
            CostType::create($validated);
        }

        unset($this->costTypes);
        Flux::modal('cost-type-form')->close();
        $this->resetForm();

        Flux::toast(variant: 'success', text: $costType ? __('Categoría actualizada.') : __('Categoría creada.'));
    }

    public function delete(CostType $costType): void
    {
        $this->authorize('delete', $costType);

        if ($this->isInUse($costType)) {
            Flux::toast(
                variant: 'danger',
                text: __('No se puede eliminar: la categoría ya tiene registros asociados.'),
            );

            return;
        }

        $costType->delete();

        unset($this->costTypes);
        Flux::toast(variant: 'success', text: __('Categoría eliminada.'));
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'scope']);
        $this->resetErrorBag();
    }

    /**
     * Kept in sync by hand with every table that has a `cost_type_id`.
     */
    private function isInUse(CostType $costType): bool
    {
        return TruckExpense::query()->where('cost_type_id', $costType->id)->exists()
            || TripExpense::query()->where('cost_type_id', $costType->id)->exists()
            || TruckFixedCost::query()->where('cost_type_id', $costType->id)->exists()
            || OverheadCost::query()->where('cost_type_id', $costType->id)->exists()
            || Maintenance::query()->where('cost_type_id', $costType->id)->exists();
    }
}
