<section class="w-full">
    <div class="flex items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Estaciones de combustible') }}</flux:heading>
            <flux:subheading>{{ __('Bombas/estaciones donde se abastecen los camiones.') }}</flux:subheading>
        </div>

        @can('create', \App\Models\FuelStation::class)
            <flux:button variant="primary" icon="plus" wire:click="create">
                {{ __('Nueva estación') }}
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
        <flux:table :paginate="$this->stations">
            <flux:table.columns>
                <flux:table.column>{{ __('Nombre') }}</flux:table.column>
                <flux:table.column>{{ __('Ubicación') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->stations as $station)
                    <flux:table.row :key="$station->id">
                        <flux:table.cell variant="strong">{{ $station->name }}</flux:table.cell>
                        <flux:table.cell>{{ $station->location ?? '—' }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-2">
                                @can('update', $station)
                                    <flux:button size="sm" variant="ghost" icon="pencil" wire:click="edit({{ $station->id }})" aria-label="{{ __('Editar') }}" />
                                @endcan
                                @can('delete', $station)
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $station->id }})" wire:confirm="{{ __('¿Eliminar esta estación?') }}" aria-label="{{ __('Eliminar') }}" />
                                @endcan
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="3" class="text-center text-zinc-500">
                            {{ __('No hay estaciones registradas todavía.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal name="fuel-station-form" class="md:w-96" @close="resetForm">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">
                {{ $editingId ? __('Editar estación') : __('Nueva estación') }}
            </flux:heading>

            <div class="space-y-4">
                <flux:input wire:model="name" :label="__('Nombre')" required />
                <flux:input wire:model="location" :label="__('Ubicación')" />
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
