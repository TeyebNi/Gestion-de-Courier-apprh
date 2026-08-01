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

    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('auth.login.logout');
    Route::get('/keep-alive', function () {
        return response()->noContent();
    })->name('keep-alive');

    // Accessible aux utilisateurs connectés (admin ET user)
    Route::get('depot', [TabdepotController::class, 'index'])->name('depot.index');
    Route::get('depot/export', [TabdepotController::class, 'exportExcel'])->name('depot.export');
    Route::post('depot', [TabdepotController::class, 'store'])->name('depot.store');
    Route::get('/export-pdf', [TabdepotController::class, 'exportPDF1']);
    Route::get('invoice', [TabdepotController::class, 'exportPDF']);
    Route::get('depot/print_reçu/{idt}',[TabdepotController::class,'print_facture'])->name('depot.print_reçu');
    Route::middleware('affectation.access')->group(function () {
        Route::get('affectation', [AffectationController::class, 'index'])->name('affectation.index');
        Route::get('affectation/export', [AffectationController::class, 'exportExcel'])->name('affectation.export');
        Route::post('affectation', [AffectationController::class, 'store'])->name('affectation.store');
        Route::put('affectation/{affectation}', [AffectationController::class, 'update'])->name('affectation.update');
        Route::delete('affectation/{affectation}', [AffectationController::class, 'destroy'])->name('affectation.destroy');
    });
    Route::put('depot/{tabdepot}', [TabdepotController::class, 'update'])->name('depot.update');
    Route::delete('depot/{tabdepot}', [TabdepotController::class, 'destroy'])->name('depot.destroy');


    Route::get('notifications', [ServiceNotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{notification}/read', [ServiceNotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('notifications/{notification}/respond', [ServiceNotificationController::class, 'respond'])->name('notifications.respond');

    // Réservé aux administrateurs (pages de configuration)
    Route::middleware('admin')->group(function () {

        Route::get('orientation', [OrientationController::class, 'index'])->name('orientation.index');
        Route::get('orientation/export', [OrientationController::class, 'exportExcel'])->name('orientation.export');
        Route::post('orientation', [OrientationController::class, 'store'])->name('orientation.store');
        Route::put('orientation/{orientation}', [OrientationController::class, 'update'])->name('orientation.update');
        Route::delete('orientation/{orientation}', [OrientationController::class, 'destroy'])->name('orientation.destroy');

        Route::get('typedem', [TypedemController::class, 'index'])->name('typedem.index');
        Route::get('typedem/export', [TypedemController::class, 'exportExcel'])->name('typedem.export');
        Route::post('typedem', [TypedemController::class, 'store'])->name('typedem.store');
        Route::put('typedem/{typedem}', [TypedemController::class, 'update'])->name('typedem.update');
        Route::delete('typedem/{typedem}', [TypedemController::class, 'destroy'])->name('typedem.destroy');

        Route::get('exportpdf', [AdminController::class, 'exportpdf'])->name('exportpdf');

        Route::get('utilisateurs', [UserController::class, 'index'])->name('users.index');
        Route::get('utilisateurs/export', [UserController::class, 'exportExcel'])->name('users.export');
        Route::put('utilisateurs/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('utilisateurs/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        
    });
});