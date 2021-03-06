<?php

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

Route::get('/dev', [App\Http\Controllers\DevController::class, 'index'])->name('dev.index');

Auth::routes();

Route::get('/', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard.index');

Route::prefix('clients')->group(function () {
    Route::get('/', [App\Http\Controllers\ClientController::class, 'index'])->name('clients.index');
    Route::get('/list', [App\Http\Controllers\ClientController::class, 'getClients'])->name('clients.list');
    Route::get('/creditcards', [App\Http\Controllers\ClientController::class, 'creditcards'])->name('clients.creditcards');
    Route::get('/creditcards/list', [App\Http\Controllers\ClientController::class, 'getCreditCards'])->name('creditcards.list');
    Route::get('/import', [App\Http\Controllers\ClientController::class, 'import_form'])->name('clients.import_form');
    Route::post('/import', [App\Http\Controllers\ClientController::class, 'import_store'])->name('clients.import_store');
    Route::get('/import-logs', [App\Http\Controllers\ClientController::class, 'import_logs'])->name('clients.import_logs');
    Route::get('/import-logs/list', [App\Http\Controllers\ClientController::class, 'getImportLogs'])->name('import.list');
});

Route::prefix('users')->group(function () {
    Route::get('/list', [App\Http\Controllers\UserController::class, 'list'])->name('users.list');
    Route::get('/add', [App\Http\Controllers\UserController::class, 'add'])->name('users.add');
});
