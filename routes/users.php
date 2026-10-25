<?php

use App\Livewire\CostTypes;
use App\Livewire\Users;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('users', Users\Index::class)->name('users.index');
    Route::livewire('cost-types', CostTypes\Index::class)->name('cost-types.index');
});
