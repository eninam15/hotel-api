<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Esta aplicación es solo API. Usa rutas bajo /api',
    ]);
});
