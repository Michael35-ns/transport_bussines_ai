<section class="w-full">
    <div class="flex items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Horas de conductores') }}</flux:heading>
            <flux:subheading>{{ __('Jornadas diarias — alimentan el pool de mano de obra general, no se atribuyen a un camión.') }}</flux:subheading>
        </div>

        @can('create', \App\Models\DriverWorklog::class)
            <flux:button variant="primary" icon="plus" wire:click="create">
                {{ __('Nueva jornada') }}
            </flux:button>
        @endcan
    </div>

    <div class="mt-6 max-w-sm">
        <flux:input
            wire:model.live.debounce.300ms="search"
            :placeholder="__('Buscar por conductor...')"
            icon="magnifying-glass"
        />
    </div>

    <div class="mt-6">
        <flux:table :paginate="$this->worklogs">
            <flux:table.columns>
                <flux:table.column>{{ __('Conductor') }}</flux:table.column>
                <flux:table.column>{{ __('Fecha') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Horas') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Tarifa usada') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Pago') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->worklogs as $worklog)
                    <flux:table.row :key="$worklog->id">
                        <flux:table.cell variant="strong">{{ $worklog->driver->name }}</flux:table.cell>
                        <flux:table.cell>{{ $worklog->work_date->format('d/m/Y') }}</flux:table.cell>
                        <flux:table.cell align="end">{{ number_format((float) $worklog->hours, 2) }}</flux:table.cell>
                        <flux:table.cell align="end">₡{{ number_format((float) $worklog->hourly_rate_snapshot, 2) }}</flux:table.cell>
                        <flux:table.cell align="end">₡{{ number_format((float) $worklog->computed_pay, 2) }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex justify-end gap-2">
                                @can('update', $worklog)
                                    <flux:button size="sm" variant="ghost" icon="pencil" wire:click="edit({{ $worklog->id }})" aria-label="{{ __('Editar') }}" />
                                @endcan
                                @can('delete', $worklog)
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $worklog->id }})" wire:confirm="{{ __('¿Eliminar esta jornada?') }}" aria-label="{{ __('Eliminar') }}" />
                                @endcan
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center text-zinc-500">
                            {{ __('No hay jornadas registradas todavía.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal name="driver-worklog-form" class="md:w-[28rem]" @close="resetForm">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">
                {{ $editingId ? __('Editar jornada') : __('Nueva jornada') }}
            </flux:heading>

            <div class="space-y-4">
                <flux:select wire:model.live="driver_id" :label="__('Conductor')" required>
                    <flux:select.option value="">{{ __('Seleccione un conductor') }}</flux:select.option>
                    @foreach ($this->driverOptions as $id => $name)
                        <flux:select.option :value="$id">{{ $name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="work_date" :label="__('Fecha')" type="date" required />
                <flux:input wire:model.live="hours" :label="__('Horas trabajadas')" type="number" step="0.25" required />

                <flux:textarea wire:model="notes" :label="__('Notas')" />
            </div>

            @if ($this->payPreview !== null)
                <flux:callout icon="calculator" color="zinc">
                    {{ __('Pago estimado (tarifa actual del conductor)') }}: <strong>₡{{ number_format($this->payPreview, 2) }}</strong>
                </flux:callout>
            @endif

            <div class="flex justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Guardar') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
