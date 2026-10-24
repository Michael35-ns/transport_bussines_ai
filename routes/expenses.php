<?php

use App\Livewire\DriverWorklogs;
use App\Livewire\FuelRecords;
use App\Livewire\FuelStations;
use App\Livewire\OverheadCosts;
use App\Livewire\TruckExpenses;
use App\Livewire\TruckFixedCosts;
use Illuminate\Support\Facades\Route;

// Temporarily disabled (owner request, 2026-10-11): Peajes (TollRecords).
// The component, policy, and data model stay intact for a quick re-enable —
// just its route and sidebar entry are removed.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('fuel-stations', FuelStations\Index::class)->name('fuel-stations.index');
    Route::livewire('fuel-records', FuelRecords\Index::class)->name('fuel-records.index');
    Route::livewire('truck-expenses', TruckExpenses\Index::class)->name('truck-expenses.index');
    Route::livewire('truck-fixed-costs', TruckFixedCosts\Index::class)->name('truck-fixed-costs.index');
    Route::livewire('overhead-costs', OverheadCosts\Index::class)->name('overhead-costs.index');
    Route::livewire('driver-worklogs', DriverWorklogs\Index::class)->name('driver-worklogs.index');
});
