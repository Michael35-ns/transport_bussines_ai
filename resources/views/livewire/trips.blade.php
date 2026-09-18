<section class="w-full">
    <div class="flex items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Viajes') }}</flux:heading>
            <flux:subheading>{{ __('Viajes por camión, conductor y ruta.') }}</flux:subheading>
        </div>

        @can('create', \App\Models\Trip::class)
            <flux:button variant="primary" icon="plus" wire:click="create">
                {{ __('Nuevo viaje') }}
            </flux:button>
        @endcan
    </div>

    <div class="mt-6 max-w-sm">
        <flux:input
            wire:model.live.debounce.300ms="search"
            :placeholder="__('Buscar por placa, conductor o ruta...')"
            icon="magnifying-glass"
        />
    </div>

    <div class="mt-6">
        <flux:table :paginate="$this->trips">
            <flux:table.columns>
                <flux:table.column>{{ __('Camión') }}</flux:table.column>
                <flux:table.column>{{ __('Conductor') }}</flux:table.column>
                <flux:table.column>{{ __('Ruta') }}</flux:table.column>
                <flux:table.column>{{ __('Fecha') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Distancia') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Precio') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->trips as $trip)
                    <flux:table.row :key="$trip->id">
                        <flux:table.cell variant="strong">{{ $trip->truck->plate }}</flux:table.cell>
                        <flux:table.cell>{{ $trip->driver->name }}</flux:table.cell>
                        <flux:table.cell>{{ $trip->route->name }}</flux:table.cell>
                        <flux:table.cell>{{ $trip->actual_end?->format('d/m/Y') ?? '—' }}</flux:table.cell>
                        <flux:table.cell align="end">
                            {{ $trip->distance !== null ? number_format((float) $trip->distance, 1).' km' : '—' }}
                            @if ($trip->distance_estimated)
                                <flux:badge size="sm" color="amber">{{ __('est.') }}</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell align="end">₡{{ number_format((float) $trip->price, 2) }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-2">
                                @can('update', $trip)
                                    <flux:button size="sm" variant="ghost" icon="pencil" wire:click="edit({{ $trip->id }})" aria-label="{{ __('Editar') }}" />
                                @endcan
                                @can('delete', $trip)
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $trip->id }})" wire:confirm="{{ __('¿Eliminar este viaje?') }}" aria-label="{{ __('Eliminar') }}" />
                                @endcan
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center text-zinc-500">
                            {{ __('No hay viajes registrados todavía.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal name="trip-form" class="md:w-[40rem]" @close="resetForm">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">
                {{ $editingId ? __('Editar viaje') : __('Nuevo viaje') }}
            </flux:heading>
            @unless ($editingId)
                <flux:subheading>{{ __('Se registra como completado con la fecha de hoy.') }}</flux:subheading>
            @endunless

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:select wire:model="truck_id" :label="__('Camión')" required>
                    <flux:select.option value="">{{ __('Seleccione un camión') }}</flux:select.option>
                    @foreach ($this->truckOptions as $id => $plate)
                        <flux:select.option :value="$id">{{ $plate }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="driver_id" :label="__('Conductor')" required>
                    <flux:select.option value="">{{ __('Seleccione un conductor') }}</flux:select.option>
                    @foreach ($this->driverOptions as $id => $name)
                        <flux:select.option :value="$id">{{ $name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="route_id" :label="__('Ruta')" required>
                    <flux:select.option value="">{{ __('Seleccione una ruta') }}</flux:select.option>
                    @foreach ($this->routeOptions as $id => $name)
                        <flux:select.option :value="$id">{{ $name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="rate_agreement_id" :label="__('Acuerdo de tarifa (opcional)')">
                    <flux:select.option value="">{{ __('Sin acuerdo — precio manual') }}</flux:select.option>
                    @foreach ($this->rateAgreementOptions as $id => $label)
                        <flux:select.option :value="$id">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:field>
                    <flux:input wire:model.blur="distance" :label="__('Distancia (km)')" type="number" step="0.01" required />
                    @if ($distance_estimated)
                        <flux:text size="sm" class="text-amber-600 dark:text-amber-400">
                            {{ __('Estimada desde la ruta. Edítala si conoces la distancia real.') }}
                        </flux:text>
                    @endif
                </flux:field>

                <flux:input wire:model="price" :label="__('Precio (₡)')" type="number" step="0.01" required />
            </div>

            @if ($rate_agreement_id === '')
                <flux:callout icon="information-circle" color="blue">
                    {{ __('Sin un acuerdo de tarifa, este viaje no podrá atribuirse a un cliente hasta que se facture.') }}
                </flux:callout>
            @endif

            <div class="flex justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Guardar') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
