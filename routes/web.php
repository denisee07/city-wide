<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
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
| One-time web installer (shared hosts have no SSH)
|--------------------------------------------------------------------------
|
| InfinityFree and similar free hosts expose no shell, so `php artisan
| migrate` cannot be run after uploading the files. This route runs the
| migrations and seeders over HTTP instead. It is locked behind a long key
| in the URL, refuses to run once the users table has data in it, and is
| skipped entirely when INSTALL_KEY is not configured (local development,
| where the CLI is available and this is never needed).
|
*/
Route::get('/install/{key}', function (string $key) {
    $expected = env('INSTALL_KEY');

    abort_if(! $expected || ! hash_equals($expected, $key), 404);

    if (\Illuminate\Support\Facades\DB::table('users')->count() > 0) {
        return response('Already installed. Nothing to do — delete INSTALL_KEY from .env if you want to disable this route.', 200);
    }

    \Artisan::call('migrate', ['--force' => true]);
    $migrateOutput = \Artisan::output();

    \Artisan::call('db:seed', ['--force' => true]);
    $seedOutput = \Artisan::output();

    return response(
        "<pre>".e($migrateOutput)."\n".e($seedOutput)."\n\nInstalled. Remove INSTALL_KEY from .env now.</pre>",
        200,
        ['Content-Type' => 'text/html']
    );
});

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

    Route::get('qr-code', [Resident\QrController::class, 'show'])->name('qr.show');

    Route::get('emergency', [Resident\EmergencyController::class, 'index'])->name('emergency.index');
    Route::get('emergency/create', [Resident\EmergencyController::class, 'create'])->name('emergency.create');
    Route::post('emergency', [Resident\EmergencyController::class, 'store'])->name('emergency.store');
    Route::get('emergency/{emergencyReport}', [Resident\EmergencyController::class, 'show'])->name('emergency.show');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    Route::get('assistance', [Resident\AssistanceController::class, 'index'])->name('assistance.index');
    Route::get('assistance/{program}', [Resident\AssistanceController::class, 'show'])->name('assistance.show');

    Route::get('events', [Resident\EventController::class, 'index'])->name('events.index');
    Route::get('events/{event}', [Resident\EventController::class, 'show'])->name('events.show');
    Route::post('events/{event}/register', [Resident\EventController::class, 'register'])->name('events.register');

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

    /* Census */
    Route::get('census', [Admin\CensusController::class, 'index'])->name('census.index');
    Route::get('census/create', [Admin\CensusController::class, 'create'])->name('census.create');
    Route::post('census', [Admin\CensusController::class, 'store'])->name('census.store');
    Route::get('census/{census}/edit', [Admin\CensusController::class, 'edit'])->name('census.edit');
    Route::put('census/{census}', [Admin\CensusController::class, 'update'])->name('census.update');
    Route::delete('census/{census}', [Admin\CensusController::class, 'destroy'])->name('census.destroy');

    /* Emergency / DRRM */
    Route::get('emergency', [Admin\EmergencyController::class, 'index'])->name('emergency.index');
    Route::get('emergency/advisories', [Admin\EmergencyController::class, 'advisories'])->name('emergency.advisories');
    Route::post('emergency/advisories', [Admin\EmergencyController::class, 'storeAdvisory'])->name('emergency.advisories.store');
    Route::delete('emergency/advisories/{advisory}', [Admin\EmergencyController::class, 'destroyAdvisory'])->name('emergency.advisories.destroy');
    Route::get('emergency/{emergencyReport}', [Admin\EmergencyController::class, 'show'])->name('emergency.show');
    Route::patch('emergency/{emergencyReport}', [Admin\EmergencyController::class, 'update'])->name('emergency.update');

    /* Notifications */
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    /* Assistance / Ayuda */
    Route::get('assistance', [Admin\AssistanceController::class, 'index'])->name('assistance.index');
    Route::get('assistance/create', [Admin\AssistanceController::class, 'create'])->name('assistance.create');
    Route::post('assistance', [Admin\AssistanceController::class, 'store'])->name('assistance.store');
    Route::get('assistance/scan', [Admin\AssistanceController::class, 'scanForm'])->name('assistance.scan');
    Route::post('assistance/scan', [Admin\AssistanceController::class, 'scanVerify'])->name('assistance.scan.verify');
    Route::post('assistance/beneficiaries/{beneficiary}/claim', [Admin\AssistanceController::class, 'scanClaim'])->name('assistance.claim');
    Route::delete('assistance/beneficiaries/{beneficiary}', [Admin\AssistanceController::class, 'removeBeneficiary'])->name('assistance.beneficiaries.remove');
    Route::post('assistance/{program}/beneficiaries', [Admin\AssistanceController::class, 'addBeneficiary'])->name('assistance.beneficiaries.add');
    Route::get('assistance/{program}', [Admin\AssistanceController::class, 'show'])->name('assistance.show');
    Route::put('assistance/{program}', [Admin\AssistanceController::class, 'update'])->name('assistance.update');
    Route::delete('assistance/{program}', [Admin\AssistanceController::class, 'destroy'])->name('assistance.destroy');

    /* Events */
    Route::get('events', [Admin\EventController::class, 'index'])->name('events.index');
    Route::get('events/create', [Admin\EventController::class, 'create'])->name('events.create');
    Route::post('events', [Admin\EventController::class, 'store'])->name('events.store');
    Route::get('events/scan', [Admin\EventController::class, 'scanForm'])->name('events.scan');
    Route::post('events/scan', [Admin\EventController::class, 'scanVerify'])->name('events.scan.verify');
    Route::post('events/registrations/{registration}/attend', [Admin\EventController::class, 'scanAttend'])->name('events.attend');
    Route::get('events/{event}', [Admin\EventController::class, 'show'])->name('events.show');
    Route::put('events/{event}', [Admin\EventController::class, 'update'])->name('events.update');
    Route::delete('events/{event}', [Admin\EventController::class, 'destroy'])->name('events.destroy');

    /* Text blast */
    Route::get('text-blast', [Admin\TextBlastController::class, 'index'])->name('textblast.index');
    Route::get('text-blast/create', [Admin\TextBlastController::class, 'create'])->name('textblast.create');
    Route::post('text-blast', [Admin\TextBlastController::class, 'store'])->name('textblast.store');

    /* Reports / Printables */
    Route::get('reports', [Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/census', [Admin\ReportController::class, 'census'])->name('reports.census');
    Route::get('reports/residents', [Admin\ReportController::class, 'residents'])->name('reports.residents');
    Route::get('reports/beneficiaries', [Admin\ReportController::class, 'beneficiaries'])->name('reports.beneficiaries');
    Route::get('reports/attendance', [Admin\ReportController::class, 'attendance'])->name('reports.attendance');
    Route::get('reports/emergency', [Admin\ReportController::class, 'emergency'])->name('reports.emergency');
});
