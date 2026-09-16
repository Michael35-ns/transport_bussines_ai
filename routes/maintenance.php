<?php

use App\Livewire\MaintenanceProviders;
use App\Livewire\Maintenances;
use App\Livewire\MaintenanceSchedules;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('maintenance-providers', MaintenanceProviders\Index::class)->name('maintenance-providers.index');
    Route::livewire('maintenance-schedules', MaintenanceSchedules\Index::class)->name('maintenance-schedules.index');
    Route::livewire('maintenances', Maintenances\Index::class)->name('maintenances.index');
});
