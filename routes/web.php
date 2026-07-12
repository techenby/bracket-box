<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::gallery')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');

    Route::livewire('brackets/create', 'pages::brackets.create')->name('brackets.create');
    Route::livewire('brackets/{bracket}/edit', 'pages::brackets.edit')->name('brackets.edit');
});

Route::livewire('brackets/{bracket:slug}', 'pages::brackets.show')
    ->middleware('voter-token')
    ->name('brackets.show');

require __DIR__.'/settings.php';
