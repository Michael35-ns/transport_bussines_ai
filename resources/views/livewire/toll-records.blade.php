<section class="w-full">
    <div class="flex items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Peajes') }}</flux:heading>
            <flux:subheading>{{ __('Pagos de peaje por camión, siempre en efectivo.') }}</flux:subheading>
        </div>

        @can('create', \App\Models\TollRecord::class)
            <flux:button variant="primary" icon="plus" wire:click="create">
                {{ __('Nuevo registro') }}
            </flux:button>
        @endcan
    </div>

    <div class="mt-6 max-w-sm">
        <flux:input
            wire:model.live.debounce.300ms="search"
            :placeholder="__('Buscar por placa...')"
            icon="magnifying-glass"
        />
    </div>

    <div class="mt-6">
        <flux:table :paginate="$this->records">
            <flux:table.columns>
                <flux:table.column>{{ __('Camión') }}</flux:table.column>
                <flux:table.column>{{ __('Ubicación') }}</flux:table.column>
                <flux:table.column>{{ __('Fecha') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Monto') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->records as $record)
                    <flux:table.row :key="$record->id">
                        <flux:table.cell variant="strong">{{ $record->truck->plate }}</flux:table.cell>
                        <flux:table.cell>{{ $record->location ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $record->occurred_at->format('d/m/Y H:i') }}</flux:table.cell>
                        <flux:table.cell align="end">₡{{ number_format((float) $record->amount, 2) }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-2">
                                @can('update', $record)
                                    <flux:button size="sm" variant="ghost" icon="pencil" wire:click="edit({{ $record->id }})" aria-label="{{ __('Editar') }}" />
                                @endcan
                                @can('delete', $record)
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $record->id }})" wire:confirm="{{ __('¿Eliminar este registro?') }}" aria-label="{{ __('Eliminar') }}" />
                                @endcan
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center text-zinc-500">
                            {{ __('No hay peajes registrados todavía.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal name="toll-record-form" class="md:w-[32rem]" @close="resetForm">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">
                {{ $editingId ? __('Editar peaje') : __('Nuevo peaje') }}
            </flux:heading>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:select wire:model.live="truck_id" :label="__('Camión')" required>
                    <flux:select.option value="">{{ __('Seleccione un camión') }}</flux:select.option>
                    @foreach ($this->truckOptions as $id => $plate)
                        <flux:select.option :value="$id">{{ $plate }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="trip_id" :label="__('Viaje (opcional)')">
                    <flux:select.option value="">{{ __('Ninguno') }}</flux:select.option>
                    @foreach ($this->tripOptions as $id => $label)
                        <flux:select.option :value="$id">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="occurred_at" :label="__('Fecha y hora')" type="datetime-local" required />
                <flux:input wire:model="location" :label="__('Ubicación')" />

                <flux:input wire:model="amount" :label="__('Monto (₡)')" type="number" step="0.01" required />

                <flux:select wire:model="payment_method" :label="__('Método de pago')">
                    @foreach ($this->paymentMethodOptions as $value => $label)
                        <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="flex justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Guardar') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
