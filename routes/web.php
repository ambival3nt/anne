<?php

use App\Http\Controllers\InitBotController;
use App\Http\Controllers\ProfileController;
use App\Http\Livewire\MessageHistory;
use App\Http\Livewire\PlaylistUI;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/botlogs', function () {
    return view('botlogs');
})->middleware(['auth', 'verified'])->name('botlogs');
//
Route::get('/history', MessageHistory::class)
    ->middleware(['auth', 'verified'])
    ->name('history');

Route::get('/prompt', \App\Http\Livewire\PromptInterface::class)
    ->middleware(['auth', 'verified'])
    ->name('prompt');

Route::get('/playlistUI', PlaylistUI::class)
    ->middleware(['auth', 'verified'])
    ->name('playlistUI');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/testInit', [InitBotController::class, 'testBotRunFromUI']);


require __DIR__.'/auth.php';
