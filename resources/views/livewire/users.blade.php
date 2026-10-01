<section class="w-full">
    <div class="flex items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Usuarios') }}</flux:heading>
            <flux:subheading>{{ __('Quién puede entrar al sistema y qué puede hacer.') }}</flux:subheading>
        </div>

        @can('create', \App\Models\User::class)
            <flux:button variant="primary" icon="plus" wire:click="create">
                {{ __('Nuevo usuario') }}
            </flux:button>
        @endcan
    </div>

    <div class="mt-6 max-w-sm">
        <flux:input
            wire:model.live.debounce.300ms="search"
            :placeholder="__('Buscar por nombre o correo...')"
            icon="magnifying-glass"
        />
    </div>

    <div class="mt-6">
        <flux:table :paginate="$this->users">
            <flux:table.columns>
                <flux:table.column>{{ __('Nombre') }}</flux:table.column>
                <flux:table.column>{{ __('Correo') }}</flux:table.column>
                <flux:table.column>{{ __('Rol') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->users as $user)
                    <flux:table.row :key="$user->id">
                        <flux:table.cell variant="strong">{{ $user->name }}</flux:table.cell>
                        <flux:table.cell>{{ $user->email }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$user->role->value === 'viewer' ? 'zinc' : 'blue'">
                                {{ $user->role->label() }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-2">
                                @can('update', $user)
                                    <flux:button size="sm" variant="ghost" icon="pencil" wire:click="edit({{ $user->id }})" aria-label="{{ __('Editar') }}" />
                                @endcan
                                @can('delete', $user)
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $user->id }})" wire:confirm="{{ __('¿Eliminar este usuario?') }}" aria-label="{{ __('Eliminar') }}" />
                                @endcan
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="text-center text-zinc-500">
                            {{ __('No hay usuarios registrados todavía.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal name="user-form" class="md:w-[28rem]" @close="resetForm">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">
                {{ $editingId ? __('Editar usuario') : __('Nuevo usuario') }}
            </flux:heading>

            <div class="space-y-4">
                <flux:input wire:model="name" :label="__('Nombre')" required />
                <flux:input wire:model="email" :label="__('Correo')" type="email" required />

                <flux:select wire:model="role" :label="__('Rol')">
                    @foreach ($this->roleOptions as $value => $label)
                        <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input
                    wire:model="password"
                    :label="$editingId ? __('Nueva contraseña (dejar en blanco para no cambiarla)') : __('Contraseña')"
                    type="password"
                    :required="! $editingId"
                />
                <flux:input
                    wire:model="password_confirmation"
                    :label="__('Confirmar contraseña')"
                    type="password"
                    :required="! $editingId"
                />
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
