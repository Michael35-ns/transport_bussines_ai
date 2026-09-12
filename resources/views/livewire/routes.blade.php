<section class="w-full">
    <div class="flex items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Rutas') }}</flux:heading>
            <flux:subheading>{{ __('Rutas de la empresa, con su distancia estándar.') }}</flux:subheading>
        </div>

        @can('create', \App\Models\Route::class)
            <flux:button variant="primary" icon="plus" wire:click="create">
                {{ __('Nueva ruta') }}
            </flux:button>
        @endcan
    </div>

    <div class="mt-6 max-w-sm">
        <flux:input
            wire:model.live.debounce.300ms="search"
            :placeholder="__('Buscar por nombre, origen o destino...')"
            icon="magnifying-glass"
        />
    </div>

    <div class="mt-6">
        <flux:table :paginate="$this->routes">
            <flux:table.columns>
                <flux:table.column>{{ __('Nombre') }}</flux:table.column>
                <flux:table.column>{{ __('Origen') }}</flux:table.column>
                <flux:table.column>{{ __('Destino') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Km estándar') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Peaje típico') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->routes as $route)
                    <flux:table.row :key="$route->id">
                        <flux:table.cell variant="strong">{{ $route->name }}</flux:table.cell>
                        <flux:table.cell>{{ $route->origin }}</flux:table.cell>
                        <flux:table.cell>{{ $route->destination }}</flux:table.cell>
                        <flux:table.cell align="end">{{ number_format((float) $route->standard_km, 1) }} km</flux:table.cell>
                        <flux:table.cell align="end">
                            {{ $route->typical_toll_cost !== null ? '₡'.number_format((float) $route->typical_toll_cost, 2) : '—' }}
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-2">
                                @can('update', $route)
                                    <flux:button size="sm" variant="ghost" icon="pencil" wire:click="edit({{ $route->id }})" aria-label="{{ __('Editar') }}" />
                                @endcan
                                @can('delete', $route)
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $route->id }})" wire:confirm="{{ __('¿Eliminar esta ruta?') }}" aria-label="{{ __('Eliminar') }}" />
                                @endcan
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center text-zinc-500">
                            {{ __('No hay rutas registradas todavía.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal name="route-form" class="md:w-[32rem]" @close="resetForm">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">
                {{ $editingId ? __('Editar ruta') : __('Nueva ruta') }}
            </flux:heading>

            <flux:callout icon="information-circle" color="blue">
                {{ __('Los kilómetros son la única fuente de distancia por viaje mientras no se registre el odómetro. Sin este dato no se pueden calcular los KPIs por km.') }}
            </flux:callout>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:input wire:model="name" :label="__('Nombre de la ruta')" class="sm:col-span-2" required />
                <flux:input wire:model="origin" :label="__('Origen')" required />
                <flux:input wire:model="destination" :label="__('Destino')" required />
                <flux:input wire:model="standard_km" :label="__('Kilómetros estándar')" type="number" step="0.01" required />
                <flux:input wire:model="typical_toll_cost" :label="__('Peaje típico (₡)')" type="number" step="0.01" />

                <flux:checkbox wire:model="is_round_trip" :label="__('Es ida y vuelta')" class="sm:col-span-2" />
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
