<?php

namespace App\Livewire\TruckExpenses;

use App\Enums\CostTypeScope;
use App\Http\Requests\StoreTruckExpenseRequest;
use App\Models\CostType;
use App\Models\Truck;
use App\Models\TruckExpense;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Gastos de camión')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $truck_id = '';

    public string $cost_type_id = '';

    public ?string $expense_date = null;

    public ?float $amount = null;

    public ?string $description = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', TruckExpense::class);
        $this->resetForm();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, TruckExpense>
     */
    #[Computed]
    public function expenses(): LengthAwarePaginator
    {
        return TruckExpense::query()
            ->with(['truck', 'costType'])
            ->when($this->search !== '', fn ($query) => $query->whereHas('truck', fn ($q) => $q->where('plate', 'like', "%{$this->search}%")))
            ->orderByDesc('expense_date')
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
     * Ad-hoc truck expenses are always classified under the "expense" cost
     * type scope (docs/business/discovery.md — Lavado, Multa, …).
     *
     * @return array<int, string>
     */
    #[Computed]
    public function costTypeOptions(): array
    {
        return CostType::query()->where('scope', CostTypeScope::Expense)->orderBy('name')->pluck('name', 'id')->all();
    }

    public function create(): void
    {
        $this->authorize('create', TruckExpense::class);

        $this->resetForm();
        Flux::modal('truck-expense-form')->show();
    }

    public function edit(TruckExpense $expense): void
    {
        $this->authorize('update', $expense);

        $this->editingId = $expense->id;
        $this->truck_id = (string) $expense->truck_id;
        $this->cost_type_id = (string) $expense->cost_type_id;
        $this->expense_date = $expense->expense_date->toDateString();
        $this->amount = (float) $expense->amount;
        $this->description = $expense->description;

        Flux::modal('truck-expense-form')->show();
    }

    public function save(): void
    {
        $expense = $this->editingId ? TruckExpense::findOrFail($this->editingId) : null;

        $this->authorize($expense ? 'update' : 'create', $expense ?? TruckExpense::class);

        $validated = $this->validate(StoreTruckExpenseRequest::buildRules());

        if ($expense) {
            $expense->update($validated);
        } else {
            // created_by is intentionally not mass-assignable — set it directly.
            $expense = new TruckExpense($validated);
            $expense->created_by = auth()->user()->id;
            $expense->save();
        }

        unset($this->expenses);
        Flux::modal('truck-expense-form')->close();
        $this->resetForm();

        Flux::toast(variant: 'success', text: $expense->wasRecentlyCreated ? __('Gasto registrado.') : __('Gasto actualizado.'));
    }

    public function delete(TruckExpense $expense): void
    {
        $this->authorize('delete', $expense);

        $expense->delete();

        unset($this->expenses);
        Flux::toast(variant: 'success', text: __('Gasto eliminado.'));
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'truck_id', 'cost_type_id', 'amount', 'description']);
        $this->expense_date = now()->toDateString();
        $this->resetErrorBag();
    }
}
