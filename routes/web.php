<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Drive\DriveController;
use App\Http\Controllers\Drive\GoogleConnectionsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('drive.index');
    }

    return redirect()->route('login');
});

Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::middleware('guest')->group(function () {
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/drive', [DriveController::class, 'index'])->name('drive.index');
    Route::get('/drive/{connection}', [DriveController::class, 'connection'])->name('drive.connection');
    Route::get('/drive/{connection}/browse', [DriveController::class, 'browse'])->name('drive.browse');
    Route::post('/drive/{connection}/folders', [DriveController::class, 'createFolder'])->name('drive.folders.create');
    Route::post('/drive/{connection}/upload', [DriveController::class, 'upload'])->name('drive.upload');
    Route::post('/drive/{connection}/items/{itemId}/rename', [DriveController::class, 'renameItem'])->name('drive.items.rename');
    Route::post('/drive/{connection}/items/{itemId}/trash', [DriveController::class, 'trashItem'])->name('drive.items.trash');
    Route::post('/drive/{connection}/items/bulk-trash', [DriveController::class, 'bulkTrashItems'])->name('drive.items.bulk_trash');
    Route::get('/drive/{connection}/items/{itemId}/preview', [DriveController::class, 'preview'])->name('drive.items.preview');
    Route::get('/drive/{connection}/items/{itemId}/download', [DriveController::class, 'download'])->name('drive.items.download');

    Route::get('/connections/google', [GoogleConnectionsController::class, 'index'])->name('connections.google.index');
    Route::get('/connections/google/add', [GoogleConnectionsController::class, 'redirect'])->name('connections.google.redirect');
    Route::get('/connections/google/callback', [GoogleConnectionsController::class, 'callback'])->name('connections.google.callback');
    Route::post('/connections/google/{connection}/default', [GoogleConnectionsController::class, 'setDefault'])->name('connections.google.default');
    Route::post('/connections/google/{connection}/rename', [GoogleConnectionsController::class, 'rename'])->name('connections.google.rename');
    Route::post('/connections/google/{connection}/disconnect', [GoogleConnectionsController::class, 'disconnect'])->name('connections.google.disconnect');
});

require __DIR__.'/auth.php';
