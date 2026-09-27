<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InventoryMovementController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SparePartBrandController;
use App\Http\Controllers\SparePartController;
use App\Http\Controllers\SystemInformationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleBrandController;
use App\Http\Controllers\VehicleController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/* Login */
Route::get('auth/login', [LoginController::class, 'index'])->name('login');
Route::post('auth/login', [LoginController::class, 'store'])->name('login.store');

/* Register */
Route::post('/auth/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')->name('logout');

Route::get('auth/register', [RegisterController::class, 'index'])->name('register');
Route::post('auth/register', [RegisterController::class, 'store'])->name('register.store');

/* ruta para confirmar cuenta email */
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect()->route('dashboard')->with('success', 'Tu correo fue verificado correctamente. Ya puedes usar el sistema SIMRH');
})->middleware(['auth', 'signed'])->name('verification.verify');

/* envio de email vista de verify */
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

/* reenvio de email */
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('success', 'Se ha enviado el correo de verificación.');
})->middleware(['auth', 'throttle:1,1'])->name('verification.send');

/* Dashboard */
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/* Clientes = customers */
Route::resource('customers', CustomerController::class)
    ->middleware(['auth', 'verified', 'module:customers']);

/* Vehiculo = vehicle */
Route::resource('vehicles', VehicleController::class)->except('show')
    ->middleware(['auth', 'verified', 'module:vehicles']);

Route::resource('vehicle-brands', VehicleBrandController::class)->except('show')
    ->middleware(['auth', 'verified', 'can:manage-security']);

/* repuestos = spareparts */
Route::resource('spareparts', SparePartController::class)->middleware(['auth', 'verified', 'module:inventory']);

Route::post('spareparts/{sparepart}/movements', [InventoryMovementController::class, 'store'])
    ->middleware(['auth', 'verified', 'module:inventory,edit'])->name('spareparts.movements.store');

Route::resource('spare-part-brands', SparePartBrandController::class)->except('show')
    ->middleware(['auth', 'verified', 'can:manage-security']);

/* facturas */
Route::resource('invoices', InvoiceController::class)->only(['index', 'create', 'store', 'show', 'destroy'])
    ->middleware(['auth', 'verified', 'module:billing']);

/* reportes operativos y bitácoras */
Route::middleware(['auth', 'verified', 'module:reports'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/billing', [ReportController::class, 'billing'])->name('billing');
    Route::get('/orders', [ReportController::class, 'orders'])->name('orders');
    Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
    Route::get('/access-logs', [ReportController::class, 'accessLogs'])->name('access');
    Route::get('/activity-logs', [ReportController::class, 'activityLogs'])->name('activity');
});

/* órdenes de trabajo */
Route::resource('orders', OrderController::class)->middleware(['auth', 'verified', 'module:orders']);
Route::post('orders/{order}/items', [OrderItemController::class, 'store'])
    ->middleware(['auth', 'verified', 'module:orders,edit'])->name('orders.items.store');
Route::put('orders/{order}/items/{orderItem}', [OrderItemController::class, 'update'])
    ->middleware(['auth', 'verified', 'module:orders,edit'])->name('orders.items.update');
Route::delete('orders/{order}/items/{orderItem}', [OrderItemController::class, 'destroy'])
    ->middleware(['auth', 'verified', 'module:orders,edit'])->name('orders.items.destroy');

/* usuarios y roles */
Route::middleware(['auth', 'verified', 'can:manage-security'])->group(function () {
    Route::resource('users', UserController::class)->except('show');
    Route::resource('roles', RoleController::class)->except('show');
});

Route::get('/about', [SystemInformationController::class, 'about'])
    ->middleware(['auth', 'verified', 'module:about'])->name('about');
Route::get('/help', [SystemInformationController::class, 'help'])
    ->middleware(['auth', 'verified', 'module:help'])->name('help');
