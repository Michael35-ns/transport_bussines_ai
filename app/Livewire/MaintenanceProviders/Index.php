<?php

namespace App\Livewire\MaintenanceProviders;

use App\Http\Requests\StoreMaintenanceProviderRequest;
use App\Models\MaintenanceProvider;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Talleres')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $name = '';

    public ?string $contact = null;

    public ?string $specialty = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', MaintenanceProvider::class);
        $this->resetForm();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, MaintenanceProvider>
     */
    #[Computed]
    public function providers(): LengthAwarePaginator
    {
        return MaintenanceProvider::query()
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(10);
    }

    public function create(): void
    {
        $this->authorize('create', MaintenanceProvider::class);

        $this->resetForm();
        Flux::modal('maintenance-provider-form')->show();
    }

    public function edit(MaintenanceProvider $provider): void
    {
        $this->authorize('update', $provider);

        $this->editingId = $provider->id;
        $this->name = $provider->name;
        $this->contact = $provider->contact;
        $this->specialty = $provider->specialty;

        Flux::modal('maintenance-provider-form')->show();
    }

    public function save(): void
    {
        $provider = $this->editingId ? MaintenanceProvider::findOrFail($this->editingId) : null;

        $this->authorize($provider ? 'update' : 'create', $provider ?? MaintenanceProvider::class);

        $validated = $this->validate((new StoreMaintenanceProviderRequest)->rules());

        if ($provider) {
            $provider->update($validated);
        } else {
            MaintenanceProvider::create($validated);
        }

        unset($this->providers);
        Flux::modal('maintenance-provider-form')->close();
        $this->resetForm();

        Flux::toast(variant: 'success', text: $provider ? __('Taller actualizado.') : __('Taller creado.'));
    }

    public function delete(MaintenanceProvider $provider): void
    {
        $this->authorize('delete', $provider);

        $provider->delete();

        unset($this->providers);
        Flux::toast(variant: 'success', text: __('Taller eliminado.'));
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'contact', 'specialty']);
        $this->resetErrorBag();
    }
}
