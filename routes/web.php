<?php
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TabdepotController;
use App\Http\Controllers\AffectationController;
use App\Http\Controllers\TypedemController;
use App\Http\Controllers\OrientationController;
use App\Http\Controllers\RemarqueController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceNotificationController;

// Routes d'authentification (login, register, mot de passe oublié...)
Auth::routes();

// Toutes les routes ci-dessous nécessitent d'être connecté
Route::middleware('auth')->group(function () {

    Route::get('/', function () {
        return view('admin/dashboard');
    })->name('dashboard');

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('auth.login.logout');
    Route::get('/keep-alive', function () {
        return response()->noContent();
    })->name('keep-alive');

    // Accessible aux utilisateurs connectés (admin ET user)
    Route::get('depot', [TabdepotController::class, 'index'])->name('depot.index');
    Route::post('depot', [TabdepotController::class, 'store'])->name('depot.store');
    Route::get('/export-pdf', [TabdepotController::class, 'exportPDF1']);
    Route::get('invoice', [TabdepotController::class, 'exportPDF']);

    Route::get('notifications', [ServiceNotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{notification}/read', [ServiceNotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('notifications/{notification}/respond', [ServiceNotificationController::class, 'respond'])->name('notifications.respond');

    // Réservé aux administrateurs (pages de configuration)
    Route::middleware('admin')->group(function () {
        Route::get('affectation', [AffectationController::class, 'index'])->name('affectation.index');
        Route::post('affectation', [AffectationController::class, 'store'])->name('affectation.store');

        Route::get('orientation', [OrientationController::class, 'index'])->name('orientation.index');
        Route::post('orientation', [OrientationController::class, 'store'])->name('orientation.store');

        Route::get('typedem', [TypedemController::class, 'index'])->name('typedem.index');
        Route::post('typedem', [TypedemController::class, 'store'])->name('typedem.store');

        Route::get('exportpdf', [AdminController::class, 'exportpdf'])->name('exportpdf');

        Route::get('utilisateurs', [UserController::class, 'index'])->name('users.index');
        Route::put('utilisateurs/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('utilisateurs/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});