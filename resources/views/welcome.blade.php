<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => __('Bienvenido')])
    </head>
    <body class="min-h-screen bg-zinc-50 antialiased dark:bg-zinc-950">
        <div class="mx-auto flex min-h-svh max-w-5xl flex-col px-6">
            <header class="flex items-center justify-between py-6">
                <div class="flex items-center gap-3">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-accent text-accent-foreground shadow-sm">
                        <x-app-logo-icon class="size-6 text-white [--logo-gap:var(--color-accent)] dark:text-zinc-900" />
                    </span>
                    <span class="font-semibold tracking-tight text-zinc-900 dark:text-white">{{ config('app.name') }}</span>
                </div>

                @auth
                    <flux:button variant="primary" :href="route('dashboard')">{{ __('Ir al panel') }}</flux:button>
                @else
                    <flux:button variant="primary" :href="route('login')">{{ __('Iniciar sesión') }}</flux:button>
                @endauth
            </header>

            <main class="flex flex-1 flex-col justify-center py-12">
                <div class="max-w-2xl">
                    <h1 class="text-4xl font-semibold tracking-tight text-zinc-900 sm:text-5xl dark:text-white">
                        {{ __('Cuánto cuesta y cuánto deja cada camión.') }}
                    </h1>
                    <p class="mt-5 text-lg text-zinc-600 dark:text-zinc-400">
                        {{ __('Control de costos, mantenimiento y rentabilidad de la flota de :name, con datos que puedes rastrear hasta su origen.', ['name' => config('app.name')]) }}
                    </p>

                    <div class="mt-8">
                        @auth
                            <flux:button variant="primary" size="base" icon-trailing="arrow-right" :href="route('dashboard')">{{ __('Ir al panel') }}</flux:button>
                        @else
                            <flux:button variant="primary" size="base" icon-trailing="arrow-right" :href="route('login')">{{ __('Iniciar sesión') }}</flux:button>
                        @endauth
                    </div>
                </div>

                <div class="mt-16 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <flux:icon.banknotes class="size-6 text-accent" />
                        <h2 class="mt-3 font-medium text-zinc-900 dark:text-white">{{ __('Costo real por camión') }}</h2>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Combustible, mantenimiento, llantas y gastos en un solo lugar.') }}</p>
                    </div>
                    <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <flux:icon.wrench-screwdriver class="size-6 text-accent" />
                        <h2 class="mt-3 font-medium text-zinc-900 dark:text-white">{{ __('Mantenimiento al día') }}</h2>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Cambios de aceite y servicios con alertas de vencidos y próximos.') }}</p>
                    </div>
                    <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <flux:icon.chart-bar class="size-6 text-accent" />
                        <h2 class="mt-3 font-medium text-zinc-900 dark:text-white">{{ __('Rentabilidad clara') }}</h2>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Utilidad y margen por camión, viaje, ruta y cliente.') }}</p>
                    </div>
                </div>
            </main>

            <footer class="py-6 text-sm text-zinc-500">
                &copy; {{ date('Y') }} {{ config('app.name') }}
            </footer>
        </div>

        @fluxScripts
    </body>
</html>
