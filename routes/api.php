<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CaseController;
use App\Http\Controllers\Api\CaseSessionController;
use App\Http\Controllers\Api\ContactUsController;
use App\Http\Controllers\Api\LawyerController;
use App\Http\Controllers\Api\LawyerDocumentController;
use App\Http\Controllers\Api\LegalController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\RolePermissionController;
use App\Http\Controllers\Api\WarningHistoryController;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
       use Illuminate\Support\Facades\Auth;



// ---------- Auth Routes --------------- 
Route::post('register', [AuthController::class , 'register']);
Route::post('login', [AuthController::class , 'login']);




// ============= Admin & Client routes =============
Route::middleware(['auth:api', 'role:admin|client'])->group(function () {

        Route::get('clients/{id}/show-overview', [ClientController::class, 'clientOverview']);

  Route::get('clients/overview', [ClientController::class, 'overview']);
    Route::get('warning-histories/client/{clientId}', [WarningHistoryController::class, 'getByClient']);


});


// ---------------- protected routes ------------- 
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout',[AuthController::class , 'logout']);
    Route::get('me',[AuthController::class , 'me']);

    /**
     * ================== Review Routes ===================
     */
    Route::get('reviews', [ReviewController::class, 'index']);
    Route::post('reviews', [ReviewController::class, 'store']);
    Route::get('reviews/{id}', [ReviewController::class, 'show']);
    Route::put('reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('reviews/{id}', [ReviewController::class, 'destroy']);

   /**
    * ============== Client Routes ============= (Role Admin )
    */
      Route::middleware('role:admin')->group(function () {
        Route::patch('cases/{id}/force-close', [CaseController::class, 'forceClose']);
        Route::patch('clients/{id}/toggle-status', [ClientController::class, 'toggleStatus']);
        Route::apiResource('clients', ClientController::class);
      

    });

});

// ============= Client can create a case =============
Route::middleware(['auth:api', 'role:client'])->group(function () {
    Route::post('cases', [CaseController::class, 'store']);
});

// ============= Public Routes =============
Route::get('lawyers', [LawyerController::class, 'index']);
Route::get('legals', [LegalController::class, 'index']);
Route::post('contact-us', [ContactUsController::class, 'store']);

// ============= Admin Only (auth:sanctum) =============
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('contact-us', [ContactUsController::class, 'index']);
    Route::get('contact-us/{id}', [ContactUsController::class, 'show']);
    Route::delete('contact-us/{id}', [ContactUsController::class, 'destroy']);

    /**
     * ================== Dashboard Routes ===================
     */
    Route::get('dashboard', [CaseController::class, 'dashboard']);
    Route::get('case-chart', [CaseController::class, 'caseChart']);

    /**
     * ================== Roles & Permissions Routes ===================
     */
    Route::get('roles', [RolePermissionController::class, 'roles']);
    Route::get('roles/{id}', [RolePermissionController::class, 'showRole']);
    Route::post('roles', [RolePermissionController::class, 'storeRole']);
    Route::put('roles/{id}', [RolePermissionController::class, 'updateRole']);
    Route::get('permissions', [RolePermissionController::class, 'permissions']);
    Route::delete('roles/{id}', [RolePermissionController::class, 'destroyRole']);
});

// ============= Admin & Avocato routes =============
Route::middleware(['auth:api', 'role:admin|avocato'])->group(function () {

    /**
     * ================== Case Routes ===================
     */
    Route::get('cases-overview', [CaseController::class , "overview"]);
    Route::apiResource('cases', CaseController::class);
    Route::post('cases/{id}/documents', [CaseController::class, 'uploadDocumentsToCase']);

    /**
     * ================== Case Session Routes ===================
     */
    Route::match(['put', 'post'], '/case-sessions/{id}', [CaseSessionController::class, 'update']);
    Route::apiResource('/case-sessions', CaseSessionController::class);

    /**
     * ================== Lawyer Routes ===================
     */
    Route::get('lawyers/statistics', [LawyerController::class, 'overviewStats']);
    Route::get('lawyers/overview', [LawyerController::class, 'overview']);
    Route::patch('lawyers/{id}/toggle-status', [LawyerController::class, 'toggleStatus']);
    Route::get('lawyers/{id}/cases', [LawyerController::class, 'getLawyerCases']);
    Route::apiResource('lawyers', LawyerController::class)->except('index');

    /**
     * ================== Legal Routes ===================
     */
    Route::apiResource('legals', LegalController::class)->except("index");

    /**
     * ================== Lawyer Document Routes (admin & avocato) ===================
     */
    Route::get('lawyer-documents/lawyer/{lawyerId}', [LawyerDocumentController::class, 'getByLawyer']);

    

    /**
     * ================== Warning History Routes ===================
     */
    Route::get('warning-histories/lawyer/{lawyerId}', [WarningHistoryController::class, 'getByLawyer']);
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






   