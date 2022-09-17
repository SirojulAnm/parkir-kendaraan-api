<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['prefix' => 'auth'], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::group(['middleware' => 'jwt.verify', 'prefix' => 'auth'], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);    
});

Route::group(['middleware' => 'jwt.verify', 'prefix' => 'plat'], function () {
    Route::get('/', [App\Http\Controllers\PlatController::class, 'getAll']);  
    Route::post('/', [App\Http\Controllers\PlatController::class, 'store']);  
    Route::post('/update', [App\Http\Controllers\PlatController::class, 'update']);
});

Route::get('/report', [App\Http\Controllers\PlatController::class, 'getReport']); 
Route::get('/generate', [App\Http\Controllers\PlatController::class, 'generateUniqCode']);   
