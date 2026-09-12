<section class="w-full">
    <div class="flex items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Camiones') }}</flux:heading>
            <flux:subheading>{{ __('Flota de camiones de la empresa.') }}</flux:subheading>
        </div>

        @can('create', \App\Models\Truck::class)
            <flux:button variant="primary" icon="plus" wire:click="create">
                {{ __('Nuevo camión') }}
            </flux:button>
        @endcan
    </div>

    <div class="mt-6 max-w-sm">
        <flux:input
            wire:model.live.debounce.300ms="search"
            :placeholder="__('Buscar por placa o número interno...')"
            icon="magnifying-glass"
        />
    </div>

    <div class="mt-6">
        <flux:table :paginate="$this->trucks">
            <flux:table.columns>
                <flux:table.column>{{ __('Placa') }}</flux:table.column>
                <flux:table.column>{{ __('N.º interno') }}</flux:table.column>
                <flux:table.column>{{ __('Tipo') }}</flux:table.column>
                <flux:table.column>{{ __('Marca / Modelo') }}</flux:table.column>
                <flux:table.column>{{ __('Odómetro (est.)') }}</flux:table.column>
                <flux:table.column>{{ __('Estado') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->trucks as $truck)
                    <flux:table.row :key="$truck->id">
                        <flux:table.cell variant="strong">{{ $truck->plate }}</flux:table.cell>
                        <flux:table.cell>{{ $truck->internal_no ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $truck->vehicle_type->label() }}</flux:table.cell>
                        <flux:table.cell>{{ trim(($truck->make ?? '').' '.($truck->model ?? '')) ?: '—' }}</flux:table.cell>
                        <flux:table.cell>{{ number_format((float) $truck->current_odometer) }} km</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="$truck->status->value === 'active' ? 'green' : 'zinc'" size="sm">
                                {{ $truck->status->label() }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-2">
                                @can('update', $truck)
                                    <flux:button size="sm" variant="ghost" icon="pencil" wire:click="edit({{ $truck->id }})" aria-label="{{ __('Editar') }}" />
                                @endcan
                                @can('delete', $truck)
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $truck->id }})" wire:confirm="{{ __('¿Eliminar este camión?') }}" aria-label="{{ __('Eliminar') }}" />
                                @endcan
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center text-zinc-500">
                            {{ __('No hay camiones registrados todavía.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal name="truck-form" class="md:w-[36rem]" @close="resetForm">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">
                {{ $editingId ? __('Editar camión') : __('Nuevo camión') }}
            </flux:heading>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:input wire:model="plate" :label="__('Placa')" required />
                <flux:input wire:model="internal_no" :label="__('N.º interno')" />

                <flux:select wire:model="vehicle_type" :label="__('Tipo de vehículo')">
                    @foreach ($this->vehicleTypeOptions as $value => $label)
                        <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="status" :label="__('Estado')">
                    @foreach ($this->statusOptions as $value => $label)
                        <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="make" :label="__('Marca')" />
                <flux:input wire:model="model" :label="__('Modelo')" />

                <flux:input wire:model="year" :label="__('Año')" type="number" />
                <flux:input wire:model="acquisition_date" :label="__('Fecha de adquisición')" type="date" />

                <flux:select wire:model="acquisition_mode" :label="__('Modo de adquisición')">
                    @foreach ($this->acquisitionModeOptions as $value => $label)
                        <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="financing_monthly" :label="__('Cuota mensual (si financiado)')" type="number" step="0.01" />

                @unless ($editingId)
                    <flux:input wire:model="current_odometer" :label="__('Odómetro inicial (km)')" type="number" step="0.01" />
                @endunless

                <flux:input wire:model="base_yard" :label="__('Base / patio')" class="sm:col-span-2" />
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
