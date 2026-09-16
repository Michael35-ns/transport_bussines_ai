<?php

use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', Dashboard\Index::class)->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/fleet.php';
require __DIR__.'/maintenance.php';
require __DIR__.'/expenses.php';
