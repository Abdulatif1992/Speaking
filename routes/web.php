<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VoiceController;
use App\Http\Controllers\QaScoreController;
use App\Http\Controllers\OpenAIController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/test', [VoiceController::class, 'test']);

// web.php
Route::get('/voice2', [VoiceController::class, 'index']);
Route::post('/voice2/upload', [VoiceController::class, 'upload']);

Route::view('/test2', 'voice.index');

Route::view('/testWepSpeech', 'voice.webSpeech');

Route::view('/assembly', 'voice.assemblyai');

Route::view('/revai', 'voice.revai')->name('revai.ui'); // UI sahifa

//ollama, baholash
Route::view('/question', 'voice.qa_score');
Route::post('/score-qa', [QaScoreController::class, 'score']);

//just design from lovable
Route::view('lovable', 'lovable/design1');

// design from chatGPT
Route::view('question2', 'openAI/design');
Route::post('/score-qa2', [OpenAIController::class, 'score'])->name('score.qa2');

