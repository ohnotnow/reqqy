<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/sso-auth.php';

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', App\Livewire\HomePage::class)->name('home');
    Route::get('/conversation', App\Livewire\ConversationPage::class)->name('conversation');

    Route::group(['middleware' => 'admin'], function () {
        Route::get('/settings', App\Livewire\SettingsPage::class)->name('settings');

        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/conversations', App\Livewire\ConversationsAdminPage::class)->name('conversations.index');
            Route::get('/conversations/{conversation_id}', App\Livewire\ConversationDetailPage::class)->name('conversations.show');
        });
    });
});
