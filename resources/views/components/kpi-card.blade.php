@props([
    'label',
    'icon' => 'chart-bar',
    'tone' => 'default',
])

@php
    $chip = match ($tone) {
        'good' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400',
        'bad' => 'bg-red-50 text-red-600 dark:bg-red-950 dark:text-red-400',
        default => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300',
    };
    $value = $tone === 'bad' ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-white';
@endphp

<div {{ $attributes->class('rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900') }}>
    <div class="flex items-center justify-between gap-2">
        <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $label }}</span>
        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ $chip }}">
            <flux:icon :name="$icon" class="size-4" />
        </span>
    </div>
    <div class="mt-2 text-2xl font-semibold tracking-tight tabular-nums {{ $value }}">{{ $slot }}</div>
</div>
