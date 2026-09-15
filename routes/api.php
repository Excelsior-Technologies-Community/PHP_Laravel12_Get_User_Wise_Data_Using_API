<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NoteController;

// Customer-wise notes API
Route::get('/notes', [NoteController::class, 'listByCustomer']);

// Customer-wise notes summary API
Route::get('/notes/summary', [NoteController::class, 'customerSummary']);

// Customer-wise notes statistics API
Route::get('/notes/statistics', [NoteController::class, 'customerStatistics']);