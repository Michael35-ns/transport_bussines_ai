@php
    $period = $this->period();
    $totals = $this->fleetTotals;
    $alerts = $this->maintenanceAlerts;
@endphp

<section class="w-full">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Dashboard') }}</flux:heading>
            <flux:subheading>{{ __('KPIs semanales de la flota (jueves a miércoles).') }}</flux:subheading>
        </div>

        <div class="flex items-center gap-2">
            <flux:button size="sm" variant="ghost" icon="chevron-left" wire:click="previousWeek" aria-label="{{ __('Semana anterior') }}" />
            <flux:text class="whitespace-nowrap">
                {{ $period->start->format('d/m/Y') }} — {{ $period->end->format('d/m/Y') }}
            </flux:text>
            <flux:button size="sm" variant="ghost" icon="chevron-right" wire:click="nextWeek" aria-label="{{ __('Semana siguiente') }}" />
            <flux:button size="sm" variant="ghost" wire:click="thisWeek">{{ __('Hoy') }}</flux:button>
        </div>
    </div>

    <flux:callout icon="information-circle" color="blue" class="mt-6">
        {{ __('Los km y las métricas por km son un estimado mientras no se capture el odómetro por viaje (ver Rutas). No son valores exactos.') }}
    </flux:callout>

    <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <flux:card size="sm">
            <flux:text size="sm" class="text-zinc-500">{{ __('Ingresos') }}</flux:text>
            <flux:heading size="lg">₡{{ number_format($totals['revenue'], 0) }}</flux:heading>
        </flux:card>
        <flux:card size="sm">
            <flux:text size="sm" class="text-zinc-500">{{ __('Costo total') }}</flux:text>
            <flux:heading size="lg">₡{{ number_format($totals['total_cost'], 0) }}</flux:heading>
        </flux:card>
        <flux:card size="sm">
            <flux:text size="sm" class="text-zinc-500">{{ __('Utilidad neta') }}</flux:text>
            <flux:heading size="lg" :class="$totals['profit_net'] < 0 ? 'text-red-600 dark:text-red-400' : ''">
                ₡{{ number_format($totals['profit_net'], 0) }}
            </flux:heading>
        </flux:card>
        <flux:card size="sm">
            <flux:text size="sm" class="text-zinc-500">{{ __('Margen') }}</flux:text>
            <flux:heading size="lg">{{ $totals['margin_pct'] !== null ? number_format($totals['margin_pct'], 1).'%' : '—' }}</flux:heading>
        </flux:card>
        <flux:card size="sm">
            <flux:text size="sm" class="text-zinc-500">{{ __('Costo / km (est.)') }}</flux:text>
            <flux:heading size="lg">{{ $totals['cost_per_km'] !== null ? '₡'.number_format($totals['cost_per_km'], 0) : '—' }}</flux:heading>
        </flux:card>
        <flux:card size="sm">
            <flux:text size="sm" class="text-zinc-500">{{ __('Ingreso / km (est.)') }}</flux:text>
            <flux:heading size="lg">{{ $totals['revenue_per_km'] !== null ? '₡'.number_format($totals['revenue_per_km'], 0) : '—' }}</flux:heading>
        </flux:card>
    </div>

    <div class="mt-6 flex flex-wrap gap-4">
        <flux:card size="sm" class="flex items-center gap-3">
            <flux:badge size="lg" color="red">{{ $alerts['overdue'] }}</flux:badge>
            <div>
                <flux:text class="font-medium">{{ __('Mantenimientos vencidos') }}</flux:text>
                <flux:text size="sm" class="text-zinc-500">{{ __('Requieren atención ya') }}</flux:text>
            </div>
        </flux:card>
        <flux:card size="sm" class="flex items-center gap-3">
            <flux:badge size="lg" color="amber">{{ $alerts['upcoming'] }}</flux:badge>
            <div>
                <flux:text class="font-medium">{{ __('Próximos a vencer') }}</flux:text>
                <flux:text size="sm" class="text-zinc-500">{{ __('Dentro del margen de aviso') }}</flux:text>
            </div>
        </flux:card>
        <flux:button variant="ghost" icon="calendar-days" :href="route('maintenance-schedules.index')" wire:navigate>
            {{ __('Ver programas') }}
        </flux:button>
    </div>

    <div class="mt-6 overflow-x-auto">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Camión') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Ingresos') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Km (est.)') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Costo total') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Utilidad') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Margen') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Costo/km') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Disponibilidad') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Utilización') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->fleetRows as $row)
                    <flux:table.row :key="$row['truck']->id">
                        <flux:table.cell variant="strong">{{ $row['truck']->plate }}</flux:table.cell>
                        <flux:table.cell align="end">₡{{ number_format($row['revenue'], 0) }}</flux:table.cell>
                        <flux:table.cell align="end">{{ number_format($row['km'], 0) }}</flux:table.cell>
                        <flux:table.cell align="end">₡{{ number_format($row['total_cost'], 0) }}</flux:table.cell>
                        <flux:table.cell align="end" :class="$row['profit_net'] < 0 ? 'text-red-600 dark:text-red-400' : ''">
                            ₡{{ number_format($row['profit_net'], 0) }}
                        </flux:table.cell>
                        <flux:table.cell align="end">{{ $row['margin_pct'] !== null ? number_format($row['margin_pct'], 1).'%' : '—' }}</flux:table.cell>
                        <flux:table.cell align="end">{{ $row['cost_per_km'] !== null ? '₡'.number_format($row['cost_per_km'], 0) : '—' }}</flux:table.cell>
                        <flux:table.cell align="end">{{ number_format($row['availability_pct'], 0) }}%</flux:table.cell>
                        <flux:table.cell align="end">{{ $row['utilization_pct'] !== null ? number_format($row['utilization_pct'], 0).'%' : '—' }}</flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="9" class="text-center text-zinc-500">
                            {{ __('No hay camiones registrados todavía.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</section>
