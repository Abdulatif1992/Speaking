<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssemblyAIController;
use App\Http\Controllers\RevAIController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/assemblyai/transcribe', [AssemblyAIController::class, 'transcribe']);
Route::get('/assemblyai/status/{id}', [AssemblyAIController::class, 'status']);

// Rev Ai
// Fayl yuklab job yaratish
Route::post('/revai/transcribe', [RevAIController::class, 'transcribe']);
// (ixtiyoriy) URL orqali job yaratish
Route::post('/revai/transcribe-url', [RevAIController::class, 'transcribeFromUrl']);
// Holat tekshirish
Route::get('/revai/status/{id}', [RevAIController::class, 'status']);
// Matnni olish (plain text)
Route::get('/revai/text/{id}', [RevAIController::class, 'textPlain']);

