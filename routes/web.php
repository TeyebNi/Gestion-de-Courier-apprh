<?php
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TabdepotController;
use App\Http\Controllers\OrientationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceNotificationController;
use App\Http\Controllers\CircuitController;

// Routes d'authentification (login uniquement : les comptes sont créés par un
// administrateur depuis "Les Utilisateurs", pas par auto-inscription publique ;
// la réinitialisation par email est désactivée, aucun SMTP réel n'est configuré
// — un administrateur réinitialise le mot de passe depuis "Les Utilisateurs").
Auth::routes(['register' => false, 'reset' => false]);

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
    Route::get('depot/print_reçu/{idt}',[TabdepotController::class,'print_facture'])->name('depot.print_reçu');
    Route::put('depot/{tabdepot}', [TabdepotController::class, 'update'])->name('depot.update');
    Route::delete('depot/{tabdepot}', [TabdepotController::class, 'destroy'])->name('depot.destroy');
    Route::get('depot-corbeille', [TabdepotController::class, 'trashed'])->name('depot.trashed');
    Route::post('depot-corbeille/{id}/restaurer', [TabdepotController::class, 'restore'])->name('depot.restore');
    Route::delete('depot-corbeille/{id}', [TabdepotController::class, 'forceDelete'])->name('depot.force-delete');
    Route::delete('depot-corbeille', [TabdepotController::class, 'emptyTrash'])->name('depot.empty-trash');

    Route::get('notifications', [ServiceNotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{notification}/read', [ServiceNotificationController::class, 'markRead'])->name('notifications.read');

    // Circuit de la demande : Accueil -> Cabinet de Maire -> (Service ou Clôturée)
    // Le Maire ne se connecte jamais à l'application : le Cabinet lui porte le
    // dossier à la main et saisit ses annotations lui-même (route "decider").
    Route::post('circuit/{tabdepot}/envoyer-fatou', [CircuitController::class, 'sendToFatou'])->name('circuit.envoyer-fatou');
    Route::get('circuit/suivi', [CircuitController::class, 'suiviIndex'])->name('circuit.suivi');

    Route::middleware('fatou')->group(function () {
        Route::get('circuit/fatou', [CircuitController::class, 'fatouIndex'])->name('circuit.fatou.index');
        Route::post('circuit/{tabdepot}/decider', [CircuitController::class, 'decide'])->name('circuit.decider');
    });

    Route::get('circuit/service', [CircuitController::class, 'serviceIndex'])->name('circuit.service.index');
    Route::post('circuit/{tabdepot}/cloturer', [CircuitController::class, 'closeDemande'])->name('circuit.cloturer');

    // Cette route générique doit rester APRÈS toutes les routes littérales ci-dessus
    // (circuit/suivi, circuit/fatou, circuit/service),
    // sinon Laravel essaierait de les faire correspondre à {tabdepot} en premier.
    Route::get('circuit/{tabdepot}/historique', [CircuitController::class, 'historique'])->name('circuit.historique');

    // Réservé aux administrateurs (pages de configuration)
    Route::middleware('admin')->group(function () {

        Route::get('orientation', [OrientationController::class, 'index'])->name('orientation.index');
        Route::get('orientation/export', [OrientationController::class, 'exportExcel'])->name('orientation.export');
        Route::post('orientation', [OrientationController::class, 'store'])->name('orientation.store');
        Route::put('orientation/{orientation}', [OrientationController::class, 'update'])->name('orientation.update');
        Route::delete('orientation/{orientation}', [OrientationController::class, 'destroy'])->name('orientation.destroy');

        Route::get('utilisateurs', [UserController::class, 'index'])->name('users.index');
        Route::get('utilisateurs/journal', [UserController::class, 'auditLog'])->name('users.audit-log');
        Route::get('utilisateurs/export', [UserController::class, 'exportExcel'])->name('users.export');
        Route::post('utilisateurs', [UserController::class, 'store'])->name('users.store');
        Route::put('utilisateurs/{user}', [UserController::class, 'update'])->name('users.update');
        Route::put('utilisateurs/{user}/mot-de-passe', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::delete('utilisateurs/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        
    });
});