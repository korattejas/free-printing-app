<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PremiumAddController;

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
Route::post('mobileOtpVerifiedRegister', [AuthController::class, 'mobileOtpVerifiedRegister']);
Route::post('sendOtp', [AuthController::class, 'sendOtp']);
Route::post('login', [AuthController::class, 'login']);
Route::get('logout', [AuthController::class, 'logout']);
Route::post('forgotPassword', [AuthController::class, 'forgotPassword']);
Route::post('mobileOtpVerifiedForgotPassword', [AuthController::class, 'mobileOtpVerifiedForgotPassword']);
Route::group(['middleware' => ['auth.jwt']], function () {
    Route::post('changePassword', [AuthController::class, 'changePassword']);
    Route::get('user', [AuthController::class, 'user']);
    Route::get('premiumAddList', [PremiumAddController::class, 'premiumAddList']);
});
Route::get('logs/admin/admin', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);

