<?php

use Illuminate\Support\Facades\Route;

// Serve the SPA container for any route and let Vue Router handle routing
Route::get('/{any?}', function () {
    return view('admin');
})->where('any', '.*');
