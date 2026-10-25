<section class="w-full">
    <div class="flex items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Categorías de costos') }}</flux:heading>
            <flux:subheading>{{ __('Rubros que aparecen al registrar gastos, costos fijos y costos generales.') }}</flux:subheading>
        </div>

        @can('create', \App\Models\CostType::class)
            <flux:button variant="primary" icon="plus" wire:click="create">
                {{ __('Nueva categoría') }}
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
        <flux:table :paginate="$this->costTypes">
            <flux:table.columns>
                <flux:table.column>{{ __('Nombre') }}</flux:table.column>
                <flux:table.column>{{ __('Se usa en') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->costTypes as $costType)
                    <flux:table.row :key="$costType->id">
                        <flux:table.cell variant="strong">{{ $costType->name }}</flux:table.cell>
                        <flux:table.cell>{{ __($costType->scope->label()) }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-2">
                                @can('update', $costType)
                                    <flux:button size="sm" variant="ghost" icon="pencil" wire:click="edit({{ $costType->id }})" aria-label="{{ __('Editar') }}" />
                                @endcan
                                @can('delete', $costType)
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $costType->id }})" wire:confirm="{{ __('¿Eliminar esta categoría?') }}" aria-label="{{ __('Eliminar') }}" />
                                @endcan
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="3" class="text-center text-zinc-500">
                            {{ __('No hay categorías registradas todavía.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal name="cost-type-form" class="md:w-96" @close="resetForm">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">
                {{ $editingId ? __('Editar categoría') : __('Nueva categoría') }}
            </flux:heading>

            <div class="space-y-4">
                <flux:input wire:model="name" :label="__('Nombre')" required />
                <flux:select wire:model="scope" :label="__('Se usa en')" required>
                    <flux:select.option value="">{{ __('Seleccione dónde se usa') }}</flux:select.option>
                    @foreach (\App\Enums\CostTypeScope::cases() as $case)
                        <flux:select.option :value="$case->value">{{ __($case->label()) }}</flux:select.option>
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
