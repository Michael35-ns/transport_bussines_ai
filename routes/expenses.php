<?php

use App\Livewire\DriverWorklogs;
use App\Livewire\FuelRecords;
use App\Livewire\FuelStations;
use App\Livewire\OverheadCosts;
use App\Livewire\TollRecords;
use App\Livewire\TruckExpenses;
use App\Livewire\TruckFixedCosts;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('fuel-stations', FuelStations\Index::class)->name('fuel-stations.index');
    Route::livewire('fuel-records', FuelRecords\Index::class)->name('fuel-records.index');
    Route::livewire('toll-records', TollRecords\Index::class)->name('toll-records.index');
    Route::livewire('truck-expenses', TruckExpenses\Index::class)->name('truck-expenses.index');
    Route::livewire('truck-fixed-costs', TruckFixedCosts\Index::class)->name('truck-fixed-costs.index');
    Route::livewire('overhead-costs', OverheadCosts\Index::class)->name('overhead-costs.index');
    Route::livewire('driver-worklogs', DriverWorklogs\Index::class)->name('driver-worklogs.index');
});
