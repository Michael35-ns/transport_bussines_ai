<section class="w-full">
    <div class="flex items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Costos fijos por camión') }}</flux:heading>
            <flux:subheading>{{ __('Seguro, permisos, fumigación, dekra, marchamo — el monto real facturado y su ciclo.') }}</flux:subheading>
        </div>

        @can('create', \App\Models\TruckFixedCost::class)
            <flux:button variant="primary" icon="plus" wire:click="create">
                {{ __('Nuevo costo') }}
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
        <flux:table :paginate="$this->costs">
            <flux:table.columns>
                <flux:table.column>{{ __('Camión') }}</flux:table.column>
                <flux:table.column>{{ __('Categoría') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Monto') }}</flux:table.column>
                <flux:table.column>{{ __('Ciclo') }}</flux:table.column>
                <flux:table.column>{{ __('Vigencia') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->costs as $cost)
                    <flux:table.row :key="$cost->id">
                        <flux:table.cell variant="strong">{{ $cost->truck->plate }}</flux:table.cell>
                        <flux:table.cell>{{ $cost->costType->name }}</flux:table.cell>
                        <flux:table.cell align="end">₡{{ number_format((float) $cost->amount, 2) }}</flux:table.cell>
                        <flux:table.cell>{{ $cost->billing_cycle->label() }}</flux:table.cell>
                        <flux:table.cell>
                            {{ $cost->effective_from->format('d/m/Y') }}
                            → {{ $cost->effective_to?->format('d/m/Y') ?? __('indefinido') }}
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-2">
                                @can('update', $cost)
                                    <flux:button size="sm" variant="ghost" icon="pencil" wire:click="edit({{ $cost->id }})" aria-label="{{ __('Editar') }}" />
                                @endcan
                                @can('delete', $cost)
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $cost->id }})" wire:confirm="{{ __('¿Eliminar este costo?') }}" aria-label="{{ __('Eliminar') }}" />
                                @endcan
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center text-zinc-500">
                            {{ __('No hay costos fijos registrados todavía.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal name="truck-fixed-cost-form" class="md:w-[32rem]" @close="resetForm">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">
                {{ $editingId ? __('Editar costo fijo') : __('Nuevo costo fijo') }}
            </flux:heading>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:select wire:model="truck_id" :label="__('Camión')" required>
                    <flux:select.option value="">{{ __('Seleccione un camión') }}</flux:select.option>
                    @foreach ($this->truckOptions as $id => $plate)
                        <flux:select.option :value="$id">{{ $plate }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="cost_type_id" :label="__('Categoría')" required>
                    <flux:select.option value="">{{ __('Seleccione una categoría') }}</flux:select.option>
                    @foreach ($this->costTypeOptions as $id => $name)
                        <flux:select.option :value="$id">{{ $name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="amount" :label="__('Monto facturado (₡)')" type="number" step="0.01" required />

                <flux:select wire:model="billing_cycle" :label="__('Ciclo de facturación')">
                    @foreach ($this->billingCycleOptions as $value => $label)
                        <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="effective_from" :label="__('Vigente desde')" type="date" required />
                <flux:input wire:model="effective_to" :label="__('Vigente hasta (opcional)')" type="date" />
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
