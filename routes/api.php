<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HistoriaClinicaController;
use App\Http\Controllers\LensometerController;
use App\Http\Controllers\RefractionController;
use App\Http\Controllers\DeviceIntegrationSettingsController;

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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');

// User management routes - only accessible by admin users
Route::apiResource('users', UserController::class)->middleware(['auth:sanctum', 'admin']);

// Historia Clinica routes - accessible by authenticated users
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('historia-clinica', HistoriaClinicaController::class);
    Route::get('search-patients', [HistoriaClinicaController::class, 'searchPatients']);
});

// Test routes without authentication middleware for debugging
Route::get('lensometer-records', [LensometerController::class, 'getRecords']);
Route::get('refraction-records', [RefractionController::class, 'getRecords']);
// Device integration settings (could be restricted later)
Route::get('device-integration-settings', [DeviceIntegrationSettingsController::class, 'index']);
Route::post('device-integration-settings/refractometer', [DeviceIntegrationSettingsController::class, 'upsertRefractometer']);
Route::post('device-integration-settings/lensometer', [DeviceIntegrationSettingsController::class, 'upsertLensometer']);

// Simple test route
Route::get('test', function() {
    return response()->json(['status' => 'API is working', 'timestamp' => now()]);
});
