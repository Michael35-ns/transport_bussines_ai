<?php

use App\Livewire\Customers;
use App\Livewire\Drivers;
use App\Livewire\Routes as RouteScreens;
use App\Livewire\Trucks;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('trucks', Trucks\Index::class)->name('trucks.index');
    Route::livewire('drivers', Drivers\Index::class)->name('drivers.index');
    Route::livewire('customers', Customers\Index::class)->name('customers.index');
    Route::livewire('routes', RouteScreens\Index::class)->name('routes.index');
});
