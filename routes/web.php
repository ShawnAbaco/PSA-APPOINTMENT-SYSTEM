<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Client\AppointmentController;


// FORCE LOGOUT - Complete session and cookie cleanup
Route::get('/force-logout', function() {
    Auth::logout();
    Session::flush();
    Session::regenerate(true);
    
    $cookies = ['laravel_session', 'XSRF-TOKEN', 'remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d'];
    foreach ($cookies as $cookie) {
        if (isset($_COOKIE[$cookie])) {
            setcookie($cookie, '', time() - 3600, '/');
        }
    }
    
    foreach ($_COOKIE as $key => $value) {
        setcookie($key, '', time() - 3600, '/');
    }
    
    return response()->json([
        'success' => true,
        'message' => 'Force logout completed!'
    ])->header('Cache-Control', 'no-cache, no-store, must-revalidate');
});

// Public routes
Route::get('/', [PageController::class, 'landing'])->name('landing');
Route::get('/appointment', [AppointmentController::class, 'index'])->name('appointment');

// Login routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    // Appointments
    Route::get('/appointments', [App\Http\Controllers\Admin\AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/{id}', [App\Http\Controllers\Admin\AppointmentController::class, 'show'])->name('appointments.show');
    Route::put('/appointments/{id}/status', [App\Http\Controllers\Admin\AppointmentController::class, 'updateStatus'])->name('appointments.status');
    Route::delete('/appointments/{id}', [App\Http\Controllers\Admin\AppointmentController::class, 'destroy'])->name('appointments.destroy');
    Route::get('/calendar', [App\Http\Controllers\Admin\AppointmentController::class, 'calendar'])->name('calendar');

    // Add to admin routes
Route::get('/slots', [App\Http\Controllers\Admin\SettingsController::class, 'manageSlots'])->name('slots.index');
Route::post('/slots', [App\Http\Controllers\Admin\SettingsController::class, 'createSlot'])->name('slots.store');
Route::put('/slots/{id}', [App\Http\Controllers\Admin\SettingsController::class, 'updateSlot'])->name('slots.update');

    
    // Users
    Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
    Route::post('/users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
    Route::put('/users/{id}/toggle-status', [App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
    
    // Reports
    Route::get('/reports', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [App\Http\Controllers\Admin\ReportController::class, 'export'])->name('reports.export');
    
    // Settings
    Route::get('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
});

// Staff routes
Route::middleware(['auth', 'staff'])->prefix('staff')->name('staff.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\Staff\DashboardController::class, 'index'])->name('dashboard');
    
    // Appointments
    Route::get('/appointments', [App\Http\Controllers\Staff\AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [App\Http\Controllers\Staff\AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [App\Http\Controllers\Staff\AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/appointments/{id}', [App\Http\Controllers\Staff\AppointmentController::class, 'show'])->name('appointments.show');
    Route::put('/appointments/{id}/confirm', [App\Http\Controllers\Staff\AppointmentController::class, 'confirm'])->name('appointments.confirm');
    Route::put('/appointments/{id}/cancel', [App\Http\Controllers\Staff\AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::put('/appointments/{id}/complete', [App\Http\Controllers\Staff\AppointmentController::class, 'complete'])->name('appointments.complete');
    
 
     // Client routes
    Route::get('/clients', [App\Http\Controllers\Staff\ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/{id}', [App\Http\Controllers\Staff\ClientController::class, 'show'])->name('clients.show');
    Route::get('/clients/{id}/details', [App\Http\Controllers\Staff\ClientController::class, 'getClientDetails'])->name('clients.details');
    Route::put('/clients/{id}', [App\Http\Controllers\Staff\ClientController::class, 'update'])->name('clients.update');
    Route::put('/clients/{id}/verify', [App\Http\Controllers\Staff\ClientController::class, 'verify'])->name('clients.verify');
    Route::put('/clients/{id}/reference', [App\Http\Controllers\Staff\ClientController::class, 'updateReferenceNumber'])->name('clients.reference');
    Route::delete('/clients/{id}', [App\Http\Controllers\Staff\ClientController::class, 'destroy'])->name('clients.destroy');
    Route::get('/clients/export/csv', [App\Http\Controllers\Staff\ClientController::class, 'export'])->name('clients.export');
    Route::get('/clients/search/ajax', [App\Http\Controllers\Staff\ClientController::class, 'search'])->name('clients.search');
    Route::get('/clients/statistics/data', [App\Http\Controllers\Staff\ClientController::class, 'statistics'])->name('clients.statistics');
});


// Client appointment routes
Route::prefix('client')->name('client.')->group(function () {
    Route::get('/appointment', [App\Http\Controllers\Client\AppointmentController::class, 'index'])->name('appointment');
    Route::post('/appointment/store', [App\Http\Controllers\Client\AppointmentController::class, 'store'])->name('appointment.store');
    Route::get('/appointment/available-dates', [App\Http\Controllers\Client\AppointmentController::class, 'getAvailableDates'])->name('appointment.available-dates');
    Route::get('/appointment/check-availability', [App\Http\Controllers\Client\AppointmentController::class, 'checkAvailability'])->name('appointment.check-availability');
});