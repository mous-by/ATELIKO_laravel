<?php

use App\Http\Controllers\Api\AffectationController;
use App\Http\Controllers\Api\AtelierController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ModeleController;
use App\Http\Controllers\Api\PaiementController;
use App\Http\Controllers\Api\RendezvousController;
use App\Http\Controllers\Api\UtilisateurController;
use Illuminate\Support\Facades\Route;

// ================================================================
// Routes API publiques (compatibles avec Spring Boot endpoints)
// ================================================================

// Health check
Route::get('/dashboard/health', [DashboardController::class, 'health']);

// Authentification
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
});

// Fichiers statiques (photos/vidéos modèles)
Route::get('/modeles/model_photo/{filename}', [ModeleController::class, 'servePhoto']);
Route::get('/modeles/videos/{filename}', [ModeleController::class, 'serveVideo']);

// ================================================================
// Routes API protégées (Bearer Token Sanctum)
// ================================================================
Route::middleware('auth:sanctum')->group(function () {

    // ---- Ateliers ----
    Route::prefix('ateliers')->group(function () {
        Route::get('/', [AtelierController::class, 'index']);
        Route::post('/', [AtelierController::class, 'store']);
        Route::get('/{id}', [AtelierController::class, 'show']);
        Route::put('/{id}', [AtelierController::class, 'update']);
        Route::delete('/{id}', [AtelierController::class, 'destroy']);
    });

    // ---- Utilisateurs ----
    Route::prefix('utilisateurs')->group(function () {
        Route::get('/', [UtilisateurController::class, 'index']);
        Route::post('/', [UtilisateurController::class, 'store']);
        Route::get('/{id}', [UtilisateurController::class, 'show']);
        Route::put('/{id}', [UtilisateurController::class, 'update']);
        Route::delete('/{id}', [UtilisateurController::class, 'destroy']);
        Route::patch('/{id}/activate', [UtilisateurController::class, 'activate']);
        Route::patch('/{id}/deactivate', [UtilisateurController::class, 'deactivate']);
        Route::post('/{id}/photo', [UtilisateurController::class, 'uploadPhoto']);
        Route::delete('/{id}/photo', [UtilisateurController::class, 'deletePhoto']);
        Route::post('/{id}/password', [UtilisateurController::class, 'changePassword']);
        Route::get('/{id}/profile', [UtilisateurController::class, 'profile']);
        Route::get('/{id}/permissions', [UtilisateurController::class, 'permissions']);
        Route::post('/{id}/permissions/sync', [UtilisateurController::class, 'syncPermissions']);
    });

    // ---- Clients ----
    Route::prefix('clients')->group(function () {
        Route::get('/', [ClientController::class, 'index']);
        Route::post('/ajouter', [ClientController::class, 'store']);
        Route::get('/synthese-mensuelle', [ClientController::class, 'syntheseMensuelle']);

        // Modèles via client
        Route::get('/modeles/atelier/{atelierId}', [ClientController::class, 'getModelesByAtelier']);
        Route::get('/modeles/{modeleId}/atelier/{atelierId}', [ClientController::class, 'getModeleDetail']);

        Route::get('/{id}', [ClientController::class, 'show']);
        Route::put('/{id}', [ClientController::class, 'update']);
        Route::put('/infos/{id}', [ClientController::class, 'update']);
        Route::delete('/{id}', [ClientController::class, 'destroy']);

        // Mesures
        Route::get('/{clientId}/mesures', [ClientController::class, 'getMesures']);
        Route::post('/{clientId}/mesures', [ClientController::class, 'addMesure']);
        Route::put('/{clientId}/mesures/{mesureId}', [ClientController::class, 'updateMesure']);
        Route::delete('/{clientId}/mesures/{mesureId}', [ClientController::class, 'deleteMesure']);
    });

    // ---- Modèles ----
    Route::prefix('modeles')->group(function () {
        Route::post('/', [ModeleController::class, 'store']);
        Route::get('/atelier/{atelierId}', [ModeleController::class, 'indexByAtelier']);
        Route::get('/atelier/{atelierId}/search', [ModeleController::class, 'search']);
        Route::get('/atelier/{atelierId}/count', [ModeleController::class, 'count']);
        Route::get('/atelier/{atelierId}/categorie/{categorie}', [ModeleController::class, 'byCategorie']);
        Route::get('/{id}/atelier/{atelierId}', [ModeleController::class, 'showByAtelier']);
        Route::put('/{id}/atelier/{atelierId}', [ModeleController::class, 'updateByAtelier']);
        Route::delete('/{id}/atelier/{atelierId}', [ModeleController::class, 'destroyByAtelier']);
        Route::post('/{id}/atelier/{atelierId}/photo', [ModeleController::class, 'uploadPhoto']);
        Route::delete('/{id}/atelier/{atelierId}/photo', [ModeleController::class, 'deletePhoto']);
        Route::patch('/{id}/atelier/{atelierId}/activate', [ModeleController::class, 'activate']);
        Route::patch('/{id}/atelier/{atelierId}/deactivate', [ModeleController::class, 'deactivate']);
    });

    // ---- Affectations ----
    Route::prefix('affectations')->group(function () {
        Route::get('/', [AffectationController::class, 'index']);
        Route::post('/', [AffectationController::class, 'store']);
        Route::get('/formulaire-data', [AffectationController::class, 'formulaireData']);
        Route::patch('/{affectationId}/statut', [AffectationController::class, 'updateStatut']);
        Route::delete('/{affectationId}', [AffectationController::class, 'destroy']);
    });

    // ---- Rendez-vous ----
    Route::prefix('rendezvous')->group(function () {
        Route::post('/', [RendezvousController::class, 'store']);
        Route::get('/{id}', [RendezvousController::class, 'show']);
        Route::put('/{id}', [RendezvousController::class, 'update']);
        Route::delete('/{id}', [RendezvousController::class, 'destroy']);
        Route::get('/atelier/{atelierId}/clients', [RendezvousController::class, 'clientsParAtelier']);
        Route::get('/atelier/{atelierId}/a-venir', [RendezvousController::class, 'aVenir']);
        Route::get('/atelier/{atelierId}/aujourdhui', [RendezvousController::class, 'aujourdhui']);
        Route::put('/{id}/confirmer', [RendezvousController::class, 'confirmer']);
        Route::put('/{id}/annuler', [RendezvousController::class, 'annuler']);
        Route::put('/{id}/terminer', [RendezvousController::class, 'terminer']);
        Route::get('/clients/{clientId}/details', [RendezvousController::class, 'clientDetails']);
    });

    // ---- Paiements ----
    Route::prefix('paiements')->group(function () {
        Route::post('/clients', [PaiementController::class, 'createPaiementClient']);
        Route::get('/clients/{clientId}', [PaiementController::class, 'getPaiementsClient']);
        Route::post('/tailleurs', [PaiementController::class, 'createPaiementTailleur']);
        Route::get('/tailleurs/{tailleurId}', [PaiementController::class, 'getPaiementsTailleur']);
        Route::get('/statistiques', [PaiementController::class, 'statistiques']);
        Route::get('/recouvrement-mensuel', [PaiementController::class, 'recouvrementMensuel']);
        Route::get('/clients/recherche', [PaiementController::class, 'rechercheClients']);
        Route::get('/recu/client/{paiementId}', [PaiementController::class, 'recuClient']);
        Route::get('/recu/client/due/{clientId}', [PaiementController::class, 'recuClientDu']);
        Route::get('/recu/tailleur/{paiementId}', [PaiementController::class, 'recuTailleur']);
        Route::post('/recu/imprimer', [PaiementController::class, 'imprimerRecu']);
    });

    // ---- Dashboard ----
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('/statistiques/{atelierId}', [DashboardController::class, 'statistiques']);
        Route::get('/tailleur/{tailleurId}/statistiques', [DashboardController::class, 'tailleurStatistiques']);
    });
});
