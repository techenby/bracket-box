<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');

    Route::livewire('brackets/create', 'pages::brackets.create')->name('brackets.create');
    Route::livewire('brackets/{bracket}/edit', 'pages::brackets.edit')->name('brackets.edit');
});

require __DIR__.'/settings.php';
