<section class="w-full">
    <div class="flex items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Mantenimientos') }}</flux:heading>
            <flux:subheading>{{ __('Historial de servicios, siempre con taller externo.') }}</flux:subheading>
        </div>

        @can('create', \App\Models\Maintenance::class)
            <flux:button variant="primary" icon="plus" wire:click="create">
                {{ __('Nuevo mantenimiento') }}
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
        <flux:table :paginate="$this->maintenances">
            <flux:table.columns>
                <flux:table.column>{{ __('Camión') }}</flux:table.column>
                <flux:table.column>{{ __('Tipo') }}</flux:table.column>
                <flux:table.column>{{ __('Taller') }}</flux:table.column>
                <flux:table.column>{{ __('Fechas') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Odómetro') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Total') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->maintenances as $maintenance)
                    <flux:table.row :key="$maintenance->id">
                        <flux:table.cell variant="strong">{{ $maintenance->truck->plate }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$maintenance->type->value === 'corrective' ? 'red' : 'blue'">
                                {{ $maintenance->type->label() }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $maintenance->provider->name }}</flux:table.cell>
                        <flux:table.cell>
                            {{ $maintenance->entry_date->format('d/m/Y') }}
                            @if ($maintenance->completion_date)
                                → {{ $maintenance->completion_date->format('d/m/Y') }}
                            @endif
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            {{ $maintenance->odometer !== null ? number_format((float) $maintenance->odometer, 0).' km' : '—' }}
                        </flux:table.cell>
                        <flux:table.cell align="end">₡{{ number_format((float) $maintenance->total, 2) }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-2">
                                @can('update', $maintenance)
                                    <flux:button size="sm" variant="ghost" icon="pencil" wire:click="edit({{ $maintenance->id }})" aria-label="{{ __('Editar') }}" />
                                @endcan
                                @can('delete', $maintenance)
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $maintenance->id }})" wire:confirm="{{ __('¿Eliminar este mantenimiento?') }}" aria-label="{{ __('Eliminar') }}" />
                                @endcan
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center text-zinc-500">
                            {{ __('No hay mantenimientos registrados todavía.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal name="maintenance-form" class="md:w-[40rem]" @close="resetForm">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">
                {{ $editingId ? __('Editar mantenimiento') : __('Nuevo mantenimiento') }}
            </flux:heading>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:select wire:model.live="truck_id" :label="__('Camión')" required>
                    <flux:select.option value="">{{ __('Seleccione un camión') }}</flux:select.option>
                    @foreach ($this->truckOptions as $id => $plate)
                        <flux:select.option :value="$id">{{ $plate }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="type" :label="__('Tipo')">
                    @foreach ($this->typeOptions as $value => $label)
                        <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="provider_id" :label="__('Taller')" required>
                    <flux:select.option value="">{{ __('Seleccione un taller') }}</flux:select.option>
                    @foreach ($this->providerOptions as $id => $name)
                        <flux:select.option :value="$id">{{ $name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="schedule_id" :label="__('Programa que cierra (opcional)')">
                    <flux:select.option value="">{{ __('Ninguno') }}</flux:select.option>
                    @foreach ($this->scheduleOptions as $id => $taskName)
                        <flux:select.option :value="$id">{{ $taskName }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="entry_date" :label="__('Fecha de ingreso')" type="date" required />
                <flux:input wire:model="completion_date" :label="__('Fecha de finalización')" type="date" />

                <flux:field>
                    <flux:input wire:model="odometer" :label="__('Odómetro real (km)')" type="number" step="0.01" />
                    <flux:text size="sm" class="text-zinc-500">
                        {{ __('Si se indica, re-ancla el estimado del camión y, si cierra un programa, su próximo vencimiento.') }}
                    </flux:text>
                </flux:field>

                <flux:select wire:model="cost_type_id" :label="__('Categoría de costo (opcional)')">
                    <flux:select.option value="">{{ __('Sin categoría') }}</flux:select.option>
                    @foreach ($this->costTypeOptions as $id => $name)
                        <flux:select.option :value="$id">{{ $name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model.blur="parts_cost" :label="__('Repuestos (₡)')" type="number" step="0.01" required />
                <flux:input wire:model.blur="labor_cost" :label="__('Mano de obra (₡)')" type="number" step="0.01" required />
                <flux:input wire:model.blur="other_cost" :label="__('Otros (₡)')" type="number" step="0.01" required />
                <flux:input wire:model="downtime_days" :label="__('Días fuera de servicio')" type="number" step="0.01" required />

                <flux:textarea wire:model="description" :label="__('Descripción')" class="sm:col-span-2" />
            </div>

            <flux:callout icon="calculator" color="zinc">
                {{ __('Total') }}: <strong>₡{{ number_format($this->totalPreview, 2) }}</strong>
            </flux:callout>

            <div class="flex justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Guardar') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
