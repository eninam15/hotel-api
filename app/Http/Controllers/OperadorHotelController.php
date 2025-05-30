<?php

namespace App\Http\Controllers;

use App\Models\OperadorHotel;
use App\Models\User;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class OperadorHotelController extends Controller
{
    public function index(): JsonResponse
    {
        $operadores = OperadorHotel::with(['user', 'hotel', 'administradores.user'])->get();
        return response()->json($operadores);
    }

    public function show($id): JsonResponse
    {
        $operador = OperadorHotel::with(['user', 'hotel', 'administradores.user'])->findOrFail($id);
        return response()->json($operador);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'hotel_id' => 'required|exists:hoteles,id',
            'nombreHotel' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verificar si ya existe un operador para este usuario y hotel
        $existe = OperadorHotel::where('user_id', $request->user_id)
            ->where('hotel_id', $request->hotel_id)
            ->exists();
        
        if ($existe) {
            return response()->json([
                'message' => 'Este usuario ya es operador de este hotel'
            ], 400);
        }

        $operador = OperadorHotel::create($request->all());
        
        // Asignar rol de operador al usuario
        $user = User::find($request->user_id);
        $user->assignRole('operador');
        
        return response()->json($operador, 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $operador = OperadorHotel::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'nombreHotel' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $operador->update($request->all());
        
        return response()->json($operador);
    }

    public function destroy($id): JsonResponse
    {
        $operador = OperadorHotel::findOrFail($id);
        
        // Eliminar primero los administradores asociados
        $operador->administradores()->delete();
        
        $operador->delete();
        
        return response()->json(null, 204);
    }

    public function operadoresPorHotel($hotelId): JsonResponse
    {
        $hotel = Hotel::findOrFail($hotelId);
        $operadores = OperadorHotel::with(['user', 'administradores.user'])
            ->where('hotel_id', $hotelId)
            ->get();
        
        return response()->json($operadores);
    }

    public function hotelesPorOperador($userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        $hoteles = OperadorHotel::with('hotel')
            ->where('user_id', $userId)
            ->get()
            ->pluck('hotel');
        
        return response()->json($hoteles);
    }
}