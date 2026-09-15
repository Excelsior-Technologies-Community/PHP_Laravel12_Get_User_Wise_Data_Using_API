<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NoteController;

Route::get('/notes', [
    NoteController::class,
    'listByCustomer'
]);

Route::get('/notes/summary', [
    NoteController::class,
    'customerSummary'
]);

Route::get('/notes/statistics', [
    NoteController::class,
    'customerStatistics'
]);

Route::get('/notes/monthly-activity', [
    NoteController::class,
    'monthlyActivity'
]);

Route::get('/notes/today', [
    NoteController::class,
    'todayNotes'
]);

Route::get('/notes/this-month', [
    NoteController::class,
    'thisMonthNotes'
]);

Route::get('/notes/latest', [
    NoteController::class,
    'latestNotes'
]);

Route::get('/notes/title-search', [
    NoteController::class,
    'titleSearch'
]);

Route::get('/notes/date-range', [
    NoteController::class,
    'dateRange'
]);

Route::get('/notes/has-notes', [
    NoteController::class,
    'hasNotes'
]);

Route::get('/notes/yearly-activity', [
    NoteController::class,
    'yearlyActivity'
]);

Route::get('/notes/{noteId}', [
    NoteController::class,
    'showCustomerNote'
]);

Route::get('/customers/note-counts', [
    NoteController::class,
    'customerNoteCounts'
]);