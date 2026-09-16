<?php

namespace App\Livewire\TollRecords;

use App\Enums\PaymentMethod;
use App\Http\Requests\StoreTollRecordRequest;
use App\Models\TollRecord;
use App\Models\Trip;
use App\Models\Truck;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Peajes')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $truck_id = '';

    public string $trip_id = '';

    public ?string $occurred_at = null;

    public ?string $location = null;

    public ?float $amount = null;

    public string $payment_method = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', TollRecord::class);
        $this->resetForm();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, TollRecord>
     */
    #[Computed]
    public function records(): LengthAwarePaginator
    {
        return TollRecord::query()
            ->with('truck')
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

    public function updatedTruckId(): void
    {
        $this->trip_id = '';
    }

    public function create(): void
    {
        $this->authorize('create', TollRecord::class);

        $this->resetForm();
        Flux::modal('toll-record-form')->show();
    }

    public function edit(TollRecord $record): void
    {
        $this->authorize('update', $record);

        $this->editingId = $record->id;
        $this->truck_id = (string) $record->truck_id;
        $this->trip_id = $record->trip_id !== null ? (string) $record->trip_id : '';
        $this->occurred_at = $record->occurred_at->format('Y-m-d\TH:i');
        $this->location = $record->location;
        $this->amount = (float) $record->amount;
        $this->payment_method = $record->payment_method->value;

        Flux::modal('toll-record-form')->show();
    }

    public function save(): void
    {
        $record = $this->editingId ? TollRecord::findOrFail($this->editingId) : null;

        $this->authorize($record ? 'update' : 'create', $record ?? TollRecord::class);

        // Built explicitly (not $this->validate()) so an unselected trip_id
        // ('' from the <select>) normalizes to null before the
        // nullable+integer rule sees it.
        $validated = Validator::make([
            'truck_id' => $this->truck_id,
            'trip_id' => $this->trip_id !== '' ? $this->trip_id : null,
            'occurred_at' => $this->occurred_at,
            'location' => $this->location,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
        ], StoreTollRecordRequest::buildRules())->validate();

        if ($record) {
            $record->update($validated);
        } else {
            // created_by is intentionally not mass-assignable — set it directly.
            $record = new TollRecord($validated);
            $record->created_by = auth()->user()->id;
            $record->save();
        }

        unset($this->records);
        Flux::modal('toll-record-form')->close();
        $this->resetForm();

        Flux::toast(variant: 'success', text: $record->wasRecentlyCreated ? __('Peaje registrado.') : __('Registro actualizado.'));
    }

    public function delete(TollRecord $record): void
    {
        $this->authorize('delete', $record);

        $record->delete();

        unset($this->records);
        Flux::toast(variant: 'success', text: __('Registro eliminado.'));
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'truck_id', 'trip_id', 'location', 'amount']);
        $this->occurred_at = now()->format('Y-m-d\TH:i');
        $this->payment_method = PaymentMethod::Cash->value;
        $this->resetErrorBag();
    }
}
