<?php

use App\Http\Controllers\Web\AbonnementWebController;
use App\Http\Controllers\Web\AdminSubscriptionWebController;
use App\Http\Controllers\Web\AffectationWebController;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\ClientWebController;
use App\Http\Controllers\Web\DashboardWebController;
use App\Http\Controllers\Web\MesureWebController;
use App\Http\Controllers\Web\ModeleWebController;
use App\Http\Controllers\Web\PaiementWebController;
use App\Http\Controllers\Web\ParametreWebController;
use App\Http\Controllers\Web\RendezvousWebController;
use App\Http\Controllers\Web\UtilisateurWebController;
use App\Http\Controllers\Web\WhatsAppWebController;
use Illuminate\Support\Facades\Route;

// ================================================================
// Authentification
// ================================================================
Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthWebController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');

// Paiement d'abonnement depuis la page de login (sans session auth)
Route::post('/abonnement/paiement-bloque', [AbonnementWebController::class, 'storeFromLogin'])->name('abonnement.paiement.blocked');

// Fallback for shared hosting where public/storage symlinks can be forbidden.
Route::get('/storage/{path}', function (string $path) {
    $root = realpath(storage_path('app/public'));
    $file = $root ? realpath($root . DIRECTORY_SEPARATOR . $path) : false;

    abort_unless(
        $root && $file && str_starts_with($file, $root . DIRECTORY_SEPARATOR) && is_file($file),
        404
    );

    return response()->file($file);
})->where('path', '.*');

// Redirection racine
Route::get('/', fn() => redirect()->route('dashboard'));

// ================================================================
// Routes protégées (utilisateur connecté)
// ================================================================
Route::middleware(['auth', 'subscription'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardWebController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/activity', [DashboardWebController::class, 'activityData'])->name('dashboard.activity');

    // ---- Abonnement (PROPRIETAIRE) ----
    Route::get('/abonnement', [AbonnementWebController::class, 'index'])->name('abonnement.index');
    Route::post('/abonnement/paiement', [AbonnementWebController::class, 'store'])->name('abonnement.paiement');
    Route::get('/abonnement/en-attente', [AbonnementWebController::class, 'pending'])->name('abonnement.pending');

    // ---- Admin Subscriptions (SUPERADMIN) ----
    Route::get('/admin/abonnements', [AdminSubscriptionWebController::class, 'index'])->name('admin.subscriptions.index');
    Route::post('/admin/subscriptions/ateliers/{atelierId}/activate', [AdminSubscriptionWebController::class, 'activateAtelierSubscription'])->name('admin.subscriptions.activate');
    Route::post('/admin/subscriptions/ateliers/{atelierId}/suspend', [AdminSubscriptionWebController::class, 'suspendAtelierSubscription'])->name('admin.subscriptions.suspend');
    Route::put('/admin/subscriptions/ateliers/{atelierId}/dates', [AdminSubscriptionWebController::class, 'updateAtelierSubscriptionDates'])->name('admin.subscriptions.dates');
    Route::post('/admin/subscriptions/payments/{paymentId}/approve', [AdminSubscriptionWebController::class, 'approveSubscriptionPayment'])->name('admin.subscriptions.payments.approve');
    Route::post('/admin/subscriptions/payments/{paymentId}/reject', [AdminSubscriptionWebController::class, 'rejectSubscriptionPayment'])->name('admin.subscriptions.payments.reject');
    Route::view('/documentation', 'documentation')->name('documentation');

    Route::prefix('parametres')->name('parametres.')->group(function () {
        Route::get('/', [ParametreWebController::class, 'index'])->name('index');
        Route::post('/ateliers', [ParametreWebController::class, 'storeAtelier'])->name('ateliers.store');
        Route::put('/ateliers/{atelier}', [ParametreWebController::class, 'updateAtelier'])->name('ateliers.update');
        Route::delete('/ateliers/{atelier}', [ParametreWebController::class, 'destroyAtelier'])->name('ateliers.destroy');
        Route::post('/abonnements/plans', [ParametreWebController::class, 'storePlan'])->name('plans.store');
        Route::put('/abonnements/plans/{plan}', [ParametreWebController::class, 'updatePlan'])->name('plans.update');
        Route::delete('/abonnements/plans/{plan}', [ParametreWebController::class, 'destroyPlan'])->name('plans.destroy');
    });

    // ---- Mesures (nouvelle commande) ----
    Route::get('/mesures/nouveau', [MesureWebController::class, 'create'])->name('mesures.create');
    Route::post('/mesures', [MesureWebController::class, 'store'])->name('mesures.store');

    // ---- Clients ----
    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [ClientWebController::class, 'index'])->name('index');
        Route::get('/nouveau', [ClientWebController::class, 'create'])->name('create');
        Route::post('/', [ClientWebController::class, 'store'])->name('store');
        Route::get('/{id}', [ClientWebController::class, 'show'])->name('show');
        Route::get('/{id}/modifier', [ClientWebController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ClientWebController::class, 'update'])->name('update');
        Route::delete('/{id}', [ClientWebController::class, 'destroy'])->name('destroy');
        Route::post('/{clientId}/mesures', [ClientWebController::class, 'ajouterMesure'])->name('mesures.store');
        Route::put('/{clientId}/mesures/{mesureId}', [ClientWebController::class, 'modifierMesure'])->name('mesures.update');
        Route::delete('/{clientId}/mesures/{mesureId}', [ClientWebController::class, 'supprimerMesure'])->name('mesures.destroy');
        Route::post('/{clientId}/paiements', [ClientWebController::class, 'ajouterPaiement'])->name('paiements.store');
        Route::get('/{clientId}/recu', [ClientWebController::class, 'recu'])->name('recu');
    });

    // ---- Modèles ----
    Route::prefix('modeles')->name('modeles.')->group(function () {
        Route::get('/', [ModeleWebController::class, 'index'])->name('index');
        Route::get('/nouveau', [ModeleWebController::class, 'create'])->name('create');
        Route::post('/', [ModeleWebController::class, 'store'])->name('store');
        Route::get('/{id}', [ModeleWebController::class, 'show'])->name('show');
        Route::get('/{id}/modifier', [ModeleWebController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ModeleWebController::class, 'update'])->name('update');
        Route::delete('/{id}', [ModeleWebController::class, 'destroy'])->name('destroy');
    });

    // ---- Affectations ----
    Route::prefix('affectations')->name('affectations.')->group(function () {
        Route::get('/', [AffectationWebController::class, 'index'])->name('index');
        Route::get('/nouvelle', [AffectationWebController::class, 'create'])->name('create');
        Route::post('/', [AffectationWebController::class, 'store'])->name('store');
        Route::patch('/{id}/statut', [AffectationWebController::class, 'updateStatut'])->name('statut');
        Route::delete('/{id}', [AffectationWebController::class, 'destroy'])->name('destroy');
    });

    // ---- Rendez-vous ----
    Route::prefix('rendezvous')->name('rendezvous.')->group(function () {
        Route::get('/', [RendezvousWebController::class, 'index'])->name('index');
        Route::post('/', [RendezvousWebController::class, 'store'])->name('store');
        Route::put('/{id}', [RendezvousWebController::class, 'update'])->name('update');
        Route::delete('/{id}', [RendezvousWebController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/statut', [RendezvousWebController::class, 'changerStatut'])->name('statut');
        Route::patch('/{id}/pret', [RendezvousWebController::class, 'marquerPret'])->name('pret');
    });

    // ---- Paiements ----
    Route::prefix('paiements')->name('paiements.')->group(function () {
        Route::get('/', [PaiementWebController::class, 'index'])->name('index');
        Route::post('/clients', [PaiementWebController::class, 'storeClient'])->name('clients.store');
        Route::post('/tailleurs', [PaiementWebController::class, 'storeTailleur'])->name('tailleurs.store');
        Route::get('/recu/client/{clientId}', [PaiementWebController::class, 'recuClient'])->name('recu.client');
        Route::get('/recu/tailleur/{tailleurId}', [PaiementWebController::class, 'recuTailleur'])->name('recu.tailleur');
        Route::post('/clients/{clientId}/sortie', [PaiementWebController::class, 'enregistrerSortie'])->name('clients.sortie');
    });

    // ---- Utilisateurs ----
    Route::prefix('utilisateurs')->name('utilisateurs.')->group(function () {
        Route::get('/', [UtilisateurWebController::class, 'index'])->name('index');
        Route::post('/', [UtilisateurWebController::class, 'store'])->name('store');
        Route::put('/{id}', [UtilisateurWebController::class, 'update'])->name('update');
        Route::delete('/{id}', [UtilisateurWebController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/activation', [UtilisateurWebController::class, 'toggleActivation'])->name('activation');
        Route::get('/{id}/permissions', [UtilisateurWebController::class, 'permissions'])->name('permissions');
        Route::post('/{id}/permissions', [UtilisateurWebController::class, 'savePermissions'])->name('permissions.save');
    });

    // ---- Profil ----
    Route::get('/profil', [UtilisateurWebController::class, 'profile'])->name('profile');
    Route::put('/profil', [UtilisateurWebController::class, 'updateProfile'])->name('profile.update');
    Route::delete('/profil/photo', [UtilisateurWebController::class, 'deletePhoto'])->name('profile.photo.delete');
    Route::post('/profil/mot-de-passe', [UtilisateurWebController::class, 'changePassword'])->name('profile.password');

    // ---- WhatsApp Business API ----
    Route::get('/whatsapp/status', [WhatsAppWebController::class, 'status'])->name('whatsapp.status');
    Route::post('/whatsapp/send-receipt', [WhatsAppWebController::class, 'sendReceipt'])->name('whatsapp.send-receipt');
});
