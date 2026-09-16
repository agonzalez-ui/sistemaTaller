<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\VehicleController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/* Login */
Route::get('auth/login',[LoginController::class, 'index'])->name('login');
Route::post('auth/login', [LoginController::class, 'store'])->name('login.store');

/* Register */
Route::get('auth/register', [RegisterController::class, 'index'])->name('register');
Route::post('auth/register', [RegisterController::class, 'store'])->name('register.store');

/* ruta para confirmar cuenta email */
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect()->route('dashboard')->with('success', 'Tu correo fue verificado correctamente. Ya puedes usar el sistema SIMRH');
})->middleware(['auth', 'signed'])->name('verification.verify');

/* envio de email vista de verify */
Route::get('/email/verify', function(){
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

/* reenvio de email */
Route::post('/email/verification-notification', function(Request $request){
    $request->user()->sendEmailVerificationNotification();

    return back()->with('success', 'Se ha enviado el correo de verificación.');
})->middleware(['auth', 'throttle:1,1'])->name('verification.send');

/* Dashboard */
Route::get('/dashboard', function(){
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard'); 

/* Clientes = customers */
Route::get('/customers', [CustomerController::class, 'index'])->middleware(['auth', 'verified'])
    ->name('customers.index');

/* Vehiculo = vehicle */
Route::get('/vehicles', [VehicleController::class, 'index'])->middleware(['auth', 'verified'])
    ->name('vehicles.index');