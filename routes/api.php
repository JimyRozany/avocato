<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CaseController;
use App\Http\Controllers\Api\CaseSessionController;
use App\Http\Controllers\Api\LawyerController;
use App\Http\Controllers\Api\LawyerDocumentController;
use App\Http\Controllers\Api\LegalController;
use App\Http\Controllers\Api\WarningHistoryController;
use Illuminate\Support\Facades\Route;



// ---------- Auth Routes --------------- 
Route::post('register', [AuthController::class , 'register']);
Route::post('login', [AuthController::class , 'login']);


// ---------------- protected routes ------------- 
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout',[AuthController::class , 'logout']);
    Route::get('me',[AuthController::class , 'me']);
   /**
    * ============== Client Routes ============= (Role Admin )
    */
      Route::middleware('role:admin')->group(function () {
        Route::get('clients/{id}/overview', [ClientController::class, 'overview']);
        Route::patch('clients/{id}/toggle-status', [ClientController::class, 'toggleStatus']);
        Route::apiResource('clients', ClientController::class);
    });

});

// ============= Client can create a case =============
Route::middleware(['auth:api', 'role:client'])->group(function () {
    Route::post('cases', [CaseController::class, 'store']);
});

// ============= Admin & Avocato routes =============
Route::middleware(['auth:api', 'role:admin|avocato'])->group(function () {

    /**
     * ================== Case Routes ===================
     */
    Route::get('cases-overview', [CaseController::class , "overview"]);
    Route::apiResource('cases', CaseController::class);

    /**
     * ================== Case Session Routes ===================
     */
    Route::match(['put', 'post'], '/case-sessions/{id}', [CaseSessionController::class, 'update']);
    Route::apiResource('/case-sessions', CaseSessionController::class);

    /**
     * ================== Lawyer Routes ===================
     */
    Route::get('lawyers/overview', [LawyerController::class, 'overview']);
    Route::patch('lawyers/{id}/toggle-status', [LawyerController::class, 'toggleStatus']);
    Route::get('lawyers/{id}/cases', [LawyerController::class, 'getLawyerCases']);
    Route::apiResource('lawyers', LawyerController::class);

    /**
     * ================== Legal Routes ===================
     */
    Route::apiResource('legals', LegalController::class);

    /**
     * ================== Lawyer Document Routes (admin & avocato) ===================
     */
    Route::get('lawyer-documents/lawyer/{lawyerId}', [LawyerDocumentController::class, 'getByLawyer']);

    

    /**
     * ================== Warning History Routes ===================
     */
    Route::patch('warning-histories/{id}/toggle-status', [WarningHistoryController::class, 'toggleStatus']);
    Route::apiResource('warning-histories', WarningHistoryController::class);

});


// ============= Avocato routes =============
Route::middleware(['auth:api', 'role:avocato'])->group(function () {
    /**
     * ================== Lawyer Document Routes ===================
     */
    Route::apiResource('lawyer-documents', LawyerDocumentController::class);
});