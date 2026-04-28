<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
    use Spatie\Permission\Models\Role;





// ---------- Auth Routes --------------- 
Route::post('register', [AuthController::class , 'register']);
Route::post('login', [AuthController::class , 'login']);


// ---------------- protected routes ------------- 
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout',[AuthController::class , 'logout']);
    Route::get('me',[AuthController::class , 'me']); // get user info 
   /**
    * ============== Client Routes ============= (Role Admin )
    */
     Route::middleware('role:admin')->group(function () {
        Route::apiResource('clients', ClientController::class);
    });
/**
 * =================== Case Routes =============
 */
// Route::middleware(['role:admin|avocato'])->group(function () {
//     Route::apiResource('cases', CaseController::class);
// });
    
});



// Route::middleware('auth:sanctum')->get("test-role", function (Request $request) {


//     $user = $request->user(); 
//     if($user->hasRole("admin"))
//         return response()->json(["data" => "is admin" ], 200);
    
//     return response()->json(["data" => "error" ], 200);

// });




Route::middleware(['auth:sanctum', 'role:admin|avocato'])->group(function () {
    Route::apiResource('cases', CaseController::class);
});


// Route::get("update-roles",function(){



// Role::where('name', 'admin')->update(['guard_name' => 'sanctum']);
// Role::where('name', 'avocato')->update(['guard_name' => 'sanctum']);
// Role::where('name', 'client')->update(['guard_name' => 'sanctum']);
// });