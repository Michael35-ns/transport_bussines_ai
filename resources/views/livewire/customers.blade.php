<section class="w-full">
    <div class="flex items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Clientes') }}</flux:heading>
            <flux:subheading>{{ __('Clientes de la empresa.') }}</flux:subheading>
        </div>

        @can('create', \App\Models\Customer::class)
            <flux:button variant="primary" icon="plus" wire:click="create">
                {{ __('Nuevo cliente') }}
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
        <flux:table :paginate="$this->customers">
            <flux:table.columns>
                <flux:table.column>{{ __('Nombre') }}</flux:table.column>
                <flux:table.column>{{ __('Cédula jurídica') }}</flux:table.column>
                <flux:table.column>{{ __('Días de crédito') }}</flux:table.column>
                <flux:table.column>{{ __('Contacto') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->customers as $customer)
                    <flux:table.row :key="$customer->id">
                        <flux:table.cell variant="strong">{{ $customer->name }}</flux:table.cell>
                        <flux:table.cell>{{ $customer->tax_id ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $customer->credit_days }}</flux:table.cell>
                        <flux:table.cell>{{ $customer->contact ?? '—' }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-2">
                                @can('update', $customer)
                                    <flux:button size="sm" variant="ghost" icon="pencil" wire:click="edit({{ $customer->id }})" aria-label="{{ __('Editar') }}" />
                                @endcan
                                @can('delete', $customer)
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $customer->id }})" wire:confirm="{{ __('¿Eliminar este cliente?') }}" aria-label="{{ __('Eliminar') }}" />
                                @endcan
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center text-zinc-500">
                            {{ __('No hay clientes registrados todavía.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal name="customer-form" class="md:w-96" @close="resetForm">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">
                {{ $editingId ? __('Editar cliente') : __('Nuevo cliente') }}
            </flux:heading>

            <div class="space-y-4">
                <flux:input wire:model="name" :label="__('Nombre')" required />
                <flux:input wire:model="tax_id" :label="__('Cédula jurídica')" />
                <flux:input wire:model="credit_days" :label="__('Días de crédito')" type="number" />
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
