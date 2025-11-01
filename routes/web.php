<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/sso-auth.php';

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', App\Livewire\HomePage::class)->name('home');
    Route::get('/conversation', App\Livewire\ConversationPage::class)->name('conversation');
    Route::get('/settings', App\Livewire\SettingsPage::class)->name('settings');
});
