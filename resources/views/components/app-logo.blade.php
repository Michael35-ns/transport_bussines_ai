@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand :name="config('app.name')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-9 items-center justify-center rounded-lg bg-accent text-accent-foreground shadow-sm">
            <x-app-logo-icon class="size-6 text-white [--logo-gap:var(--color-accent)] dark:text-zinc-900" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="config('app.name')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-9 items-center justify-center rounded-lg bg-accent text-accent-foreground shadow-sm">
            <x-app-logo-icon class="size-6 text-white [--logo-gap:var(--color-accent)] dark:text-zinc-900" />
        </x-slot>
    </flux:brand>
@endif
