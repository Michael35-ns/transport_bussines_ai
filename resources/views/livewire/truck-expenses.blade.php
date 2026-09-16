<section class="w-full">
    <div class="flex items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Gastos de camión') }}</flux:heading>
            <flux:subheading>{{ __('Gastos puntuales por camión (lavado, multas, etc.), no ligados a un viaje.') }}</flux:subheading>
        </div>

        @can('create', \App\Models\TruckExpense::class)
            <flux:button variant="primary" icon="plus" wire:click="create">
                {{ __('Nuevo gasto') }}
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
        <flux:table :paginate="$this->expenses">
            <flux:table.columns>
                <flux:table.column>{{ __('Camión') }}</flux:table.column>
                <flux:table.column>{{ __('Categoría') }}</flux:table.column>
                <flux:table.column>{{ __('Fecha') }}</flux:table.column>
                <flux:table.column>{{ __('Descripción') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Monto') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->expenses as $expense)
                    <flux:table.row :key="$expense->id">
                        <flux:table.cell variant="strong">{{ $expense->truck->plate }}</flux:table.cell>
                        <flux:table.cell>{{ $expense->costType->name }}</flux:table.cell>
                        <flux:table.cell>{{ $expense->expense_date->format('d/m/Y') }}</flux:table.cell>
                        <flux:table.cell>{{ $expense->description ?? '—' }}</flux:table.cell>
                        <flux:table.cell align="end">₡{{ number_format((float) $expense->amount, 2) }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-2">
                                @can('update', $expense)
                                    <flux:button size="sm" variant="ghost" icon="pencil" wire:click="edit({{ $expense->id }})" aria-label="{{ __('Editar') }}" />
                                @endcan
                                @can('delete', $expense)
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $expense->id }})" wire:confirm="{{ __('¿Eliminar este gasto?') }}" aria-label="{{ __('Eliminar') }}" />
                                @endcan
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center text-zinc-500">
                            {{ __('No hay gastos registrados todavía.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal name="truck-expense-form" class="md:w-[32rem]" @close="resetForm">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">
                {{ $editingId ? __('Editar gasto') : __('Nuevo gasto') }}
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

                <flux:input wire:model="expense_date" :label="__('Fecha')" type="date" required />
                <flux:input wire:model="amount" :label="__('Monto (₡)')" type="number" step="0.01" required />

                <flux:textarea wire:model="description" :label="__('Descripción')" class="sm:col-span-2" />
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
