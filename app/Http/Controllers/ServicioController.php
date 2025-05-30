<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class ServicioController extends Controller
{
    public function index(): JsonResponse
    {
        $servicios = Servicio::all();
        return response()->json($servicios);
    }

    public function show($id): JsonResponse
    {
        $servicio = Servicio::with('hoteles')->findOrFail($id);
        return response()->json($servicio);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:servicios',
            'comision' => 'nullable|numeric|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $servicio = Servicio::create($request->all());
        return response()->json($servicio, 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $servicio = Servicio::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:255|unique:servicios,nombre,' . $id,
            'comision' => 'nullable|numeric|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $servicio->update($request->all());
        return response()->json($servicio);
    }

    public function destroy($id): JsonResponse
    {
        $servicio = Servicio::findOrFail($id);
        
        // Verificar si el servicio está en uso antes de eliminarlo
        if ($servicio->hoteles()->count() > 0 || $servicio->ofertas()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el servicio porque está en uso'
            ], 400);
        }
        
        $servicio->delete();
        return response()->json(null, 204);
    }
}