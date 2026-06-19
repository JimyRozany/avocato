<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CaseController;
use App\Http\Controllers\Api\CaseSessionController;
use App\Http\Controllers\Api\ContactUsController;
use App\Http\Controllers\Api\LawyerController;
use App\Http\Controllers\Api\LawyerDocumentController;
use App\Http\Controllers\Api\LegalBotController;
use App\Http\Controllers\Api\LegalController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\RolePermissionController;
use App\Http\Controllers\Api\WarningHistoryController;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;



// ---------- Auth Routes --------------- 
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);




// ============= Admin & Client routes =============
Route::middleware(['auth:api', 'role:admin|client'])->group(function () {

   

    Route::get('clients/overview', [ClientController::class, 'overview']);
    Route::get('clients/{id}/cases', [ClientController::class, 'getClientCases']);
   

    Route::apiResource('clients', ClientController::class)->except("index" ,'show');
});


// ---------------- protected routes ------------- 
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    Route::get("clients", [ClientController::class, "index"]);

    Route::get('cases', [CaseController::class, 'index']);
    Route::post('cases', [CaseController::class, 'store']);
    Route::get('cases/{id}', [CaseController::class, 'show']);
    Route::delete('cases/{id}', [CaseController::class, 'destroy']);
    Route::put('cases/{id}', [CaseController::class, 'update']);
    Route::post('cases/{id}/documents', [CaseController::class, 'uploadDocumentsToCase']);
    Route::get('cases-overview', [CaseController::class, "overview"]);
    Route::patch('cases/{id}/force-close', [CaseController::class, 'forceClose']);

      Route::get('clients/{id}', [ClientController::class , "show"]);
       Route::get('clients/{id}/show-overview', [ClientController::class, 'clientOverview']);
    Route::patch('warning-histories/{id}/toggle-status', [WarningHistoryController::class, 'toggleStatus']);

     Route::get('warning-histories/client/{clientId}', [WarningHistoryController::class, 'getByClient']);
    Route::get('warning-histories/lawyer/{lawyerId}', [WarningHistoryController::class, 'getByLawyer']);



    // Route::get('case-sessions' , [CaseSessionController::class , 'index']) ;
    Route::get('case-sessions/case/{caseId}', [CaseSessionController::class, 'getByCase']);
    Route::get('case-sessions/{caseSessionId}', [CaseSessionController::class, 'show']);


    /**
     * ================== Dashboard Routes ===================
     */
    Route::get('dashboard', [CaseController::class, 'dashboard']);
    Route::get('case-chart', [CaseController::class, 'caseChart']);

    /**
     * ================== Review Routes ===================
     */
    Route::get('reviews', [ReviewController::class, 'index']);
    Route::get('reviews/user/{userId}', [ReviewController::class, 'userReviews']);
    Route::post('reviews', [ReviewController::class, 'store']);
    Route::get('reviews/{id}', [ReviewController::class, 'show']);
    Route::put('reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('reviews/{id}', [ReviewController::class, 'destroy']);

    /**
     * ============== Client Routes ============= (Role Admin )
     */
    Route::middleware('role:admin')->group(function () {

        Route::patch('clients/{id}/toggle-status', [ClientController::class, 'toggleStatus']);
    });
});



// ============= Public Routes =============
Route::get('lawyers', [LawyerController::class, 'index']);
Route::get('legals', [LegalController::class, 'index']);
Route::post('legal-bot/ask', [LegalBotController::class, 'ask']);
Route::post('contact-us', [ContactUsController::class, 'store']);

// ============= Admin Only (auth:sanctum) =============
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('contact-us', [ContactUsController::class, 'index']);
    Route::get('contact-us/{id}', [ContactUsController::class, 'show']);
    Route::put('contact-us/{id}', [ContactUsController::class, 'update']);
    Route::patch('contact-us/{id}/close', [ContactUsController::class, 'close']);
    Route::delete('contact-us/{id}', [ContactUsController::class, 'destroy']);



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

    Route::patch('cases/{id}/status', [CaseController::class, 'changeStatus']);


    /**
     * ================== Case Session Routes ===================
     */
    Route::match(['put', 'post'], '/case-sessions/{id}', [CaseSessionController::class, 'update']);
    Route::apiResource('case-sessions', CaseSessionController::class)->except("index", 'show');

    /**
     * ================== Lawyer Routes ===================
     */
    Route::get('lawyers/statistics', [LawyerController::class, 'overviewStats']);
    Route::get('lawyers/overview', [LawyerController::class, 'overview']);
    Route::patch('lawyers/{id}/toggle-status', [LawyerController::class, 'toggleStatus']);
    Route::get('lawyers/{id}/cases', [LawyerController::class, 'getLawyerCases']);
    Route::get('lawyers/{id}/clients', [LawyerController::class, 'getLawyerClients']);
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
    Route::apiResource('warning-histories', WarningHistoryController::class);
});


// ============= Avocato routes =============
Route::middleware(['auth:api', 'role:avocato'])->group(function () {
    /**
     * ================== Lawyer Document Routes ===================
     */
    Route::apiResource('lawyer-documents', LawyerDocumentController::class);
});






//    Route::get("check-role" , function(){

//     $user = auth()->user();

//     return response()->json($user
//  , 200);
//    })->middleware("auth:sanctum");