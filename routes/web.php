<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\SurveyController;
use Illuminate\Support\Facades\Route;

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

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', [AuthenticatedSessionController::class, 'create'])
    ->name('login');

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth'])->name('dashboard');
Route::get('/dashboard', [SurveyController::class, 'dashboard'])->middleware(['auth'])->name('dashboard');
Route::get('/take-survey/{id}', [SurveyController::class, 'takeSurvey'])->middleware(['auth'])->name('take-survey');
Route::get('/view-survey/{id}', [SurveyController::class, 'viewSurvey'])->middleware(['auth'])->name('view-survey');
Route::get('/download-pdf/{id}', [SurveyController::class, 'downloadPdf'])->middleware(['auth'])->name('download-pdf');
Route::get('/start-survey', [SurveyController::class, 'startSurvey'])->middleware(['auth'])->name('start-survey');
Route::post('/store-survey/{id}', [SurveyController::class, 'storeSurvey'])->middleware(['auth'])->name('store-survey');
Route::post('/update-survey/{id}', [SurveyController::class, 'updateSurvey'])->middleware(['auth'])->name('update-survey');
Route::post('/submit-survey', [SurveyController::class, 'submitSurvey'])->middleware(['auth'])->name('submit-survey');
Route::get('/finish-survey/{id}', [SurveyController::class, 'finishSurvey'])->middleware(['auth'])->name('finish-survey');
Route::post('/close-survey/{id}', [SurveyController::class, 'closeSurvey'])->middleware(['auth'])->name('close-survey');


require __DIR__ . '/auth.php';
