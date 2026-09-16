<section class="w-full">
    <div class="flex items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Mantenimientos preventivos') }}</flux:heading>
            <flux:subheading>{{ __('Programas por camión (hoy: cambio de aceite cada 5,000 km).') }}</flux:subheading>
        </div>

        @can('create', \App\Models\MaintenanceSchedule::class)
            <flux:button variant="primary" icon="plus" wire:click="create">
                {{ __('Nuevo programa') }}
            </flux:button>
        @endcan
    </div>

    <div class="mt-6 max-w-sm">
        <flux:input
            wire:model.live.debounce.300ms="search"
            :placeholder="__('Buscar por placa o tarea...')"
            icon="magnifying-glass"
        />
    </div>

    <div class="mt-6">
        <flux:table :paginate="$this->schedules">
            <flux:table.columns>
                <flux:table.column>{{ __('Camión') }}</flux:table.column>
                <flux:table.column>{{ __('Tarea') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Cada') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Próximo (km est.)') }}</flux:table.column>
                <flux:table.column>{{ __('Estado') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->schedules as $schedule)
                    <flux:table.row :key="$schedule->id">
                        <flux:table.cell variant="strong">{{ $schedule->truck->plate }}</flux:table.cell>
                        <flux:table.cell>
                            {{ $schedule->task_name }}
                            @unless ($schedule->active)
                                <flux:badge size="sm" color="zinc">{{ __('inactivo') }}</flux:badge>
                            @endunless
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            {{ $schedule->interval_value !== null ? number_format((float) $schedule->interval_value, 0).' '.$schedule->interval_type->label() : '—' }}
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            {{ $schedule->nextDueOdometer() !== null ? number_format($schedule->nextDueOdometer(), 0).' km' : '—' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            @php $status = $schedule->status(); @endphp
                            <flux:badge size="sm" :color="match ($status) {
                                'VENCIDO' => 'red',
                                'PROXIMO' => 'amber',
                                default => 'green',
                            }">
                                {{ $status }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-2">
                                @can('update', $schedule)
                                    <flux:button size="sm" variant="ghost" icon="pencil" wire:click="edit({{ $schedule->id }})" aria-label="{{ __('Editar') }}" />
                                @endcan
                                @can('delete', $schedule)
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $schedule->id }})" wire:confirm="{{ __('¿Eliminar este programa?') }}" aria-label="{{ __('Eliminar') }}" />
                                @endcan
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center text-zinc-500">
                            {{ __('No hay programas de mantenimiento registrados todavía.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal name="maintenance-schedule-form" class="md:w-[32rem]" @close="resetForm">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">
                {{ $editingId ? __('Editar programa') : __('Nuevo programa') }}
            </flux:heading>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:select wire:model="truck_id" :label="__('Camión')" class="sm:col-span-2" required>
                    <flux:select.option value="">{{ __('Seleccione un camión') }}</flux:select.option>
                    @foreach ($this->truckOptions as $id => $plate)
                        <flux:select.option :value="$id">{{ $plate }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="task_name" :label="__('Tarea')" class="sm:col-span-2" required />

                <flux:select wire:model="interval_type" :label="__('Tipo de intervalo')">
                    @foreach ($this->intervalTypeOptions as $value => $label)
                        <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="interval_value" :label="__('Cada cuánto')" type="number" step="0.01" />

                <flux:input wire:model="last_done_odometer" :label="__('Última vez a (km, si aplica)')" type="number" step="0.01" />
                <flux:input wire:model="lead_km" :label="__('Aviso previo (km)')" type="number" step="0.01" required />

                <flux:checkbox wire:model="active" :label="__('Activo')" class="sm:col-span-2" />
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
