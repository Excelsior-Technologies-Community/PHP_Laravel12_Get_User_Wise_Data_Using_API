<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NoteController;

Route::get('/notes', [NoteController::class, 'listByCustomer']);
