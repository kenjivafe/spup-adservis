<?php

use App\Models\JobOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Http\Controllers\Auth\EmailVerificationController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Spatie\Browsershot\Browsershot;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/welcome', function () {
    return view('welcome');
});

Route::get('/email/verify/{id}/{hash}', EmailVerificationController::class)
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');
