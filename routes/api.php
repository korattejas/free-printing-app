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
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::post('register', [AuthController::class, 'register']);
Route::post('mobileOtpVerified', [AuthController::class, 'mobileOtpVerified']);
Route::post('sendOtp', [AuthController::class, 'sendOtp']);
Route::post('login', [AuthController::class, 'login']);
Route::get('logout', [AuthController::class, 'logout']);
Route::group(['middleware' => ['auth.jwt']], function () {
    Route::post('changePassword', [AuthController::class, 'changePassword']);
    Route::get('user', [AuthController::class, 'user']);


});

