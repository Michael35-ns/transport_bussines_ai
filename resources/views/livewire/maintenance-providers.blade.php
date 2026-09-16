<section class="w-full">
    <div class="flex items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Talleres') }}</flux:heading>
            <flux:subheading>{{ __('Talleres externos de mantenimiento (la empresa no tiene taller propio).') }}</flux:subheading>
        </div>

        @can('create', \App\Models\MaintenanceProvider::class)
            <flux:button variant="primary" icon="plus" wire:click="create">
                {{ __('Nuevo taller') }}
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
        <flux:table :paginate="$this->providers">
            <flux:table.columns>
                <flux:table.column>{{ __('Nombre') }}</flux:table.column>
                <flux:table.column>{{ __('Especialidad') }}</flux:table.column>
                <flux:table.column>{{ __('Contacto') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->providers as $provider)
                    <flux:table.row :key="$provider->id">
                        <flux:table.cell variant="strong">{{ $provider->name }}</flux:table.cell>
                        <flux:table.cell>{{ $provider->specialty ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $provider->contact ?? '—' }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-2">
                                @can('update', $provider)
                                    <flux:button size="sm" variant="ghost" icon="pencil" wire:click="edit({{ $provider->id }})" aria-label="{{ __('Editar') }}" />
                                @endcan
                                @can('delete', $provider)
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $provider->id }})" wire:confirm="{{ __('¿Eliminar este taller?') }}" aria-label="{{ __('Eliminar') }}" />
                                @endcan
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="text-center text-zinc-500">
                            {{ __('No hay talleres registrados todavía.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal name="maintenance-provider-form" class="md:w-96" @close="resetForm">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">
                {{ $editingId ? __('Editar taller') : __('Nuevo taller') }}
            </flux:heading>

            <div class="space-y-4">
                <flux:input wire:model="name" :label="__('Nombre')" required />
                <flux:input wire:model="specialty" :label="__('Especialidad')" :placeholder="__('general, llantas, frenos, motor...')" />
                <flux:input wire:model="contact" :label="__('Contacto')" />
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
