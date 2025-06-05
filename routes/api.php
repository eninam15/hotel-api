<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HabitacionController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\ReservaController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Rutas públicas para hoteles
Route::get('/hoteles', [HotelController::class, 'index']);
Route::get('/hoteles/{id}', [HotelController::class, 'show']);
Route::post('/hoteles/buscar', [HotelController::class, 'buscar']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user',    [AuthController::class, 'user']);
    
    // Rutas protegidas para Hoteles (CRUD administrativo)
    Route::post('/hoteles', [HotelController::class, 'store']);
    Route::put('/hoteles/{id}', [HotelController::class, 'update']);
    Route::delete('/hoteles/{id}', [HotelController::class, 'destroy']);
    
    // Rutas para Habitaciones
    Route::get('/habitaciones', [HabitacionController::class, 'index']);
    Route::get('/habitaciones/hotel/{hotelId}', [HabitacionController::class, 'index']);
    Route::get('/habitaciones/{id}', [HabitacionController::class, 'show']);
    Route::post('/habitaciones', [HabitacionController::class, 'store']);
    Route::put('/habitaciones/{id}', [HabitacionController::class, 'update']);
    Route::delete('/habitaciones/{id}', [HabitacionController::class, 'destroy']);
    
    // Rutas para gestión de disponibilidad
    Route::post('/habitaciones/{id}/disponibilidad', [HabitacionController::class, 'disponibilidad']);
    Route::post('/habitaciones/check-disponibilidad', [HabitacionController::class, 'checkDisponibilidad']);

    // Rutas para Reservas
    Route::get('/reservas', [ReservaController::class, 'index']);
    Route::get('/reservas/{id}', [ReservaController::class, 'show']);
    Route::post('/reservas', [ReservaController::class, 'store']);
    Route::put('/reservas/{id}', [ReservaController::class, 'update']);
    Route::get('/mis-reservas', [ReservaController::class, 'misReservas']);
    Route::get('/hoteles/{hotelId}/reservas', [ReservaController::class, 'reservasPorHotel']);
});