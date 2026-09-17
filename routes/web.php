<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('customer-dashboard');
});

Route::get('/dashboard', function () {
    return view('customer-dashboard');
});

Route::get('/api-playground', function () {
    return view('customer-dashboard');
});
