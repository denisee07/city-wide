<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Resident;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public landing
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/features', [HomeController::class, 'features'])->name('features');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.attempt');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Resident interface
|--------------------------------------------------------------------------
*/

Route::prefix('resident')->name('resident.')->middleware(['auth', 'role:resident'])->group(function () {
    Route::get('/', [Resident\DashboardController::class, 'index'])->name('dashboard');

    Route::get('complaints', [Resident\ComplaintController::class, 'index'])->name('complaints.index');
    Route::get('complaints/create', [Resident\ComplaintController::class, 'create'])->name('complaints.create');
    Route::post('complaints', [Resident\ComplaintController::class, 'store'])->name('complaints.store');
    Route::get('complaints/{complaint}', [Resident\ComplaintController::class, 'show'])->name('complaints.show');

    Route::get('freedom-wall', [Resident\FreedomWallController::class, 'index'])->name('freedom-wall.index');
    Route::post('freedom-wall', [Resident\FreedomWallController::class, 'store'])->name('freedom-wall.store');
    Route::post('freedom-wall/{post}/react', [Resident\FreedomWallController::class, 'react'])->name('freedom-wall.react');
    Route::post('freedom-wall/{post}/delete', [Resident\FreedomWallController::class, 'destroy'])->name('freedom-wall.destroy');

    Route::get('documents', [Resident\DocumentRequestController::class, 'index'])->name('documents.index');
    Route::get('documents/create', [Resident\DocumentRequestController::class, 'create'])->name('documents.create');
    Route::post('documents', [Resident\DocumentRequestController::class, 'store'])->name('documents.store');
    Route::get('documents/{documentRequest}', [Resident\DocumentRequestController::class, 'show'])->name('documents.show');
    Route::get('documents/{documentRequest}/certificate', [Resident\DocumentRequestController::class, 'certificate'])->name('documents.certificate');

    Route::get('appointments', [Resident\AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('appointments/create', [Resident\AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('appointments', [Resident\AppointmentController::class, 'store'])->name('appointments.store');
    Route::post('appointments/{appointment}/cancel', [Resident\AppointmentController::class, 'cancel'])->name('appointments.cancel');

    Route::get('queue', [Resident\QueueController::class, 'index'])->name('queue.index');
    Route::post('queue', [Resident\QueueController::class, 'store'])->name('queue.store');
    Route::post('queue/leave', [Resident\QueueController::class, 'leave'])->name('queue.leave');

    Route::get('jobs', [Resident\JobController::class, 'index'])->name('jobs.index');
    Route::get('jobs/{job}', [Resident\JobController::class, 'show'])->name('jobs.show');

    Route::get('announcements', [Resident\AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('announcements/{announcement}', [Resident\AnnouncementController::class, 'show'])->name('announcements.show');

    Route::get('lost-found', [Resident\LostFoundController::class, 'index'])->name('lost-found.index');
    Route::post('lost-found', [Resident\LostFoundController::class, 'store'])->name('lost-found.store');

    Route::get('map', [Resident\MapController::class, 'index'])->name('map.index');

    Route::get('profile', [Resident\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [Resident\ProfileController::class, 'update'])->name('profile.update');
});

/*
|--------------------------------------------------------------------------
| Admin portal
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| City Administrator interface
|--------------------------------------------------------------------------
|
| One console for every officer. 'official' covers the two retired
| barangay-level roles still held by existing accounts, so an officer signs
| in here no matter which value their row carries. Residents who reach any of
| these URLs are redirected by EnsureRole, not shown a 403.
|
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:official'])->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    /* The 18-barangay overview, open to every officer. */
    Route::get('barangays', [Admin\BarangayController::class, 'index'])->name('barangays.index');

    Route::get('complaints', [Admin\ComplaintController::class, 'index'])->name('complaints.index');
    Route::get('complaints/{complaint}', [Admin\ComplaintController::class, 'show'])->name('complaints.show');
    Route::patch('complaints/{complaint}', [Admin\ComplaintController::class, 'update'])->name('complaints.update');
    Route::post('complaints/{complaint}/notes', [Admin\ComplaintController::class, 'note'])->name('complaints.note');

    Route::resource('announcements', Admin\AnnouncementController::class)->except(['show']);
    Route::get('announcements/{announcement}/preview', [Admin\AnnouncementController::class, 'preview'])->name('announcements.preview');

    Route::resource('lost-found', Admin\LostFoundController::class)->except(['show']);
    Route::patch('lost-found/{lostFound}/status', [Admin\LostFoundController::class, 'status'])->name('lost-found.status');

    Route::get('requests', [Admin\DocumentRequestController::class, 'index'])->name('requests.index');
    Route::get('requests/{documentRequest}', [Admin\DocumentRequestController::class, 'show'])->name('requests.show');
    Route::patch('requests/{documentRequest}', [Admin\DocumentRequestController::class, 'update'])->name('requests.update');
    Route::get('requests/{documentRequest}/certificate', [Admin\DocumentRequestController::class, 'certificate'])->name('requests.certificate');

    Route::get('appointments', [Admin\AppointmentController::class, 'index'])->name('appointments.index');
    Route::patch('appointments/{appointment}', [Admin\AppointmentController::class, 'update'])->name('appointments.update');

    Route::get('queue', [Admin\QueueController::class, 'index'])->name('queue.index');
    Route::post('queue', [Admin\QueueController::class, 'store'])->name('queue.store');
    Route::post('queue/call-next', [Admin\QueueController::class, 'callNext'])->name('queue.call-next');
    Route::patch('queue/{queueTicket}', [Admin\QueueController::class, 'update'])->name('queue.update');
    Route::post('queue/reset', [Admin\QueueController::class, 'reset'])->name('queue.reset');

    Route::resource('jobs', Admin\JobController::class)->except(['show']);

    Route::get('users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::patch('users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');

    Route::get('map', [Admin\MapController::class, 'index'])->name('map.index');

    Route::get('settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
    Route::post('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
});
