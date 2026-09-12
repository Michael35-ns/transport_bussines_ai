<?php

namespace App\Livewire\Drivers;

use App\Enums\ActiveStatus;
use App\Http\Requests\StoreDriverRequest;
use App\Models\Driver;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Conductores')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $name = '';

    public ?string $document_id = null;

    public ?string $license_class = null;

    public ?string $license_expiry = null;

    public ?string $hire_date = null;

    public ?float $hourly_rate = null;

    public string $status = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', Driver::class);
        $this->resetForm();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, Driver>
     */
    #[Computed]
    public function drivers(): LengthAwarePaginator
    {
        return Driver::query()
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(10);
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function statusOptions(): array
    {
        return collect(ActiveStatus::cases())->mapWithKeys(fn (ActiveStatus $case) => [$case->value => $case->label()])->all();
    }

    public function create(): void
    {
        $this->authorize('create', Driver::class);

        $this->resetForm();
        Flux::modal('driver-form')->show();
    }

    public function edit(Driver $driver): void
    {
        $this->authorize('update', $driver);

        $this->editingId = $driver->id;
        $this->name = $driver->name;
        $this->document_id = $driver->document_id;
        $this->license_class = $driver->license_class;
        $this->license_expiry = $driver->license_expiry?->toDateString();
        $this->hire_date = $driver->hire_date?->toDateString();
        $this->hourly_rate = (float) $driver->hourly_rate;
        $this->status = $driver->status->value;

        Flux::modal('driver-form')->show();
    }

    public function save(): void
    {
        $driver = $this->editingId ? Driver::findOrFail($this->editingId) : null;

        $this->authorize($driver ? 'update' : 'create', $driver ?? Driver::class);

        $validated = $this->validate((new StoreDriverRequest)->rules());

        if ($driver) {
            $driver->update($validated);
        } else {
            Driver::create($validated);
        }

        unset($this->drivers);
        Flux::modal('driver-form')->close();
        $this->resetForm();

        Flux::toast(variant: 'success', text: $driver ? __('Conductor actualizado.') : __('Conductor creado.'));
    }

    public function delete(Driver $driver): void
    {
        $this->authorize('delete', $driver);

        $driver->delete();

        unset($this->drivers);
        Flux::toast(variant: 'success', text: __('Conductor eliminado.'));
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'document_id', 'license_class', 'license_expiry', 'hire_date', 'hourly_rate']);
        $this->status = ActiveStatus::Active->value;
        $this->resetErrorBag();
    }
}
