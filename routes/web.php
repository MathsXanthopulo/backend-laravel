<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RedirectActionController;
use App\Http\Controllers\RedirectViewController;

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

// Redirect management page
Route::get('/redirects', [RedirectViewController::class, 'index'])->name('redirects.index');

// Redirect route
Route::get('/r/{code}', [RedirectActionController::class, 'redirect']);
