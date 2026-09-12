<?php

namespace App\Livewire\Customers;

use App\Http\Requests\StoreCustomerRequest;
use App\Models\Customer;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Clientes')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $name = '';

    public ?string $tax_id = null;

    public ?int $credit_days = null;

    public ?string $contact = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', Customer::class);
        $this->resetForm();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, Customer>
     */
    #[Computed]
    public function customers(): LengthAwarePaginator
    {
        return Customer::query()
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(10);
    }

    public function create(): void
    {
        $this->authorize('create', Customer::class);

        $this->resetForm();
        Flux::modal('customer-form')->show();
    }

    public function edit(Customer $customer): void
    {
        $this->authorize('update', $customer);

        $this->editingId = $customer->id;
        $this->name = $customer->name;
        $this->tax_id = $customer->tax_id;
        $this->credit_days = $customer->credit_days;
        $this->contact = $customer->contact;

        Flux::modal('customer-form')->show();
    }

    public function save(): void
    {
        $customer = $this->editingId ? Customer::findOrFail($this->editingId) : null;

        $this->authorize($customer ? 'update' : 'create', $customer ?? Customer::class);

        $validated = $this->validate((new StoreCustomerRequest)->rules());

        if ($customer) {
            $customer->update($validated);
        } else {
            Customer::create($validated);
        }

        unset($this->customers);
        Flux::modal('customer-form')->close();
        $this->resetForm();

        Flux::toast(variant: 'success', text: $customer ? __('Cliente actualizado.') : __('Cliente creado.'));
    }

    public function delete(Customer $customer): void
    {
        $this->authorize('delete', $customer);

        $customer->delete();

        unset($this->customers);
        Flux::toast(variant: 'success', text: __('Cliente eliminado.'));
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'tax_id', 'credit_days', 'contact']);
        $this->resetErrorBag();
    }
}
