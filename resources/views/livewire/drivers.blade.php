<section class="w-full">
    <div class="flex items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Conductores') }}</flux:heading>
            <flux:subheading>{{ __('Conductores de la empresa.') }}</flux:subheading>
        </div>

        @can('create', \App\Models\Driver::class)
            <flux:button variant="primary" icon="plus" wire:click="create">
                {{ __('Nuevo conductor') }}
            </flux:button>
        @endcan
    </div>

    <div class="mt-6 max-w-sm">
        <flux:input
            wire:model.live.debounce.300ms="search"
            :placeholder="__('Buscar por nombre...')"
            icon="magnifying-glass"
        />
    </div>

    <div class="mt-6">
        <flux:table :paginate="$this->drivers">
            <flux:table.columns>
                <flux:table.column>{{ __('Nombre') }}</flux:table.column>
                <flux:table.column>{{ __('Identificación') }}</flux:table.column>
                <flux:table.column>{{ __('Licencia') }}</flux:table.column>
                <flux:table.column>{{ __('Tarifa/hora') }}</flux:table.column>
                <flux:table.column>{{ __('Estado') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->drivers as $driver)
                    <flux:table.row :key="$driver->id">
                        <flux:table.cell variant="strong">{{ $driver->name }}</flux:table.cell>
                        <flux:table.cell>{{ $driver->document_id ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $driver->license_class ?? '—' }}</flux:table.cell>
                        <flux:table.cell>₡{{ number_format((float) $driver->hourly_rate, 2) }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="$driver->status->value === 'active' ? 'green' : 'zinc'" size="sm">
                                {{ $driver->status->label() }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-2">
                                @can('update', $driver)
                                    <flux:button size="sm" variant="ghost" icon="pencil" wire:click="edit({{ $driver->id }})" aria-label="{{ __('Editar') }}" />
                                @endcan
                                @can('delete', $driver)
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $driver->id }})" wire:confirm="{{ __('¿Eliminar este conductor?') }}" aria-label="{{ __('Eliminar') }}" />
                                @endcan
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center text-zinc-500">
                            {{ __('No hay conductores registrados todavía.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal name="driver-form" class="md:w-[32rem]" @close="resetForm">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">
                {{ $editingId ? __('Editar conductor') : __('Nuevo conductor') }}
            </flux:heading>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:input wire:model="name" :label="__('Nombre completo')" class="sm:col-span-2" required />
                <flux:input wire:model="document_id" :label="__('Identificación')" />
                <flux:input wire:model="license_class" :label="__('Clase de licencia')" />
                <flux:input wire:model="license_expiry" :label="__('Vencimiento de licencia')" type="date" />
                <flux:input wire:model="hire_date" :label="__('Fecha de contratación')" type="date" />
                <flux:input wire:model="hourly_rate" :label="__('Tarifa por hora (₡)')" type="number" step="0.0001" required />

                <flux:select wire:model="status" :label="__('Estado')">
                    @foreach ($this->statusOptions as $value => $label)
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
