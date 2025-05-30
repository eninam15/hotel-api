<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Disponibilidad;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class HabitacionController extends Controller
{
    public function index($hotelId = null): JsonResponse
    {
        $query = Habitacion::with('hotel');
        
        if ($hotelId) {
            $query->where('hotel_id', $hotelId);
        }
        
        $habitaciones = $query->get();
        return response()->json($habitaciones);
    }

    public function show($id): JsonResponse
    {
        $habitacion = Habitacion::with([
            'hotel', 
            'disponibilidades' => function($query) {
                $query->where('fecha', '>=', Carbon::today()->format('Y-m-d'))
                      ->orderBy('fecha');
            },
            'ofertas' => function($query) {
                $query->where('fec_fin', '>=', Carbon::today()->format('Y-m-d'));
            }
        ])->findOrFail($id);
        
        return response()->json($habitacion);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'hotel_id' => 'required|exists:hoteles,id',
            'nombre' => 'required|string|max:255',
            'tipo_habitacion' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'nro_adultos' => 'required|integer|min:1',
            'nro_ninos' => 'required|integer|min:0',
            'descripcion' => 'nullable|string',
            'disponibilidad' => 'sometimes|array',
            'disponibilidad.fecha_inicio' => 'required_with:disponibilidad|date',
            'disponibilidad.fecha_fin' => 'required_with:disponibilidad|date|after_or_equal:disponibilidad.fecha_inicio',
            'disponibilidad.cantidad' => 'required_with:disponibilidad|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $habitacion = Habitacion::create($request->except('disponibilidad'));

        // Crear disponibilidad si se proporciona
        if ($request->has('disponibilidad')) {
            $fechaInicio = Carbon::parse($request->disponibilidad['fecha_inicio']);
            $fechaFin = Carbon::parse($request->disponibilidad['fecha_fin']);
            $cantidad = $request->disponibilidad['cantidad'];
            $precio = $request->precio; // Usar el precio de la habitación por defecto

            // Crear un registro de disponibilidad para cada día en el rango
            for ($fecha = $fechaInicio; $fecha->lte($fechaFin); $fecha->addDay()) {
                Disponibilidad::create([
                    'habitacion_id' => $habitacion->id,
                    'fecha' => $fecha->format('Y-m-d'),
                    'disponibles' => $cantidad,
                    'reservadas' => 0,
                    'precio' => $precio
                ]);
            }
        }

        return response()->json($habitacion, 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $habitacion = Habitacion::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'hotel_id' => 'sometimes|required|exists:hoteles,id',
            'nombre' => 'sometimes|required|string|max:255',
            'tipo_habitacion' => 'sometimes|required|string|max:255',
            'precio' => 'sometimes|required|numeric|min:0',
            'nro_adultos' => 'sometimes|required|integer|min:1',
            'nro_ninos' => 'sometimes|required|integer|min:0',
            'descripcion' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $habitacion->update($request->all());
        
        return response()->json($habitacion);
    }

    public function destroy($id): JsonResponse
    {
        $habitacion = Habitacion::findOrFail($id);
        $habitacion->delete();
        
        return response()->json(null, 204);
    }

    public function disponibilidad($id, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'cantidad' => 'required|integer|min:1',
            'precio' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $habitacion = Habitacion::findOrFail($id);
        $fechaInicio = Carbon::parse($request->fecha_inicio);
        $fechaFin = Carbon::parse($request->fecha_fin);
        $cantidad = $request->cantidad;
        $precio = $request->precio ?? $habitacion->precio;

        // Crear o actualizar disponibilidad para cada día en el rango
        for ($fecha = $fechaInicio; $fecha->lte($fechaFin); $fecha->addDay()) {
            $disponibilidad = Disponibilidad::updateOrCreate(
                [
                    'habitacion_id' => $habitacion->id,
                    'fecha' => $fecha->format('Y-m-d')
                ],
                [
                    'disponibles' => $cantidad,
                    'precio' => $precio
                ]
            );
        }

        return response()->json([
            'message' => 'Disponibilidad actualizada correctamente',
            'habitacion_id' => $habitacion->id,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin
        ]);
    }

    public function checkDisponibilidad(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'habitacion_id' => 'required|exists:habitaciones,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'cantidad' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $habitacionId = $request->habitacion_id;
        $fechaInicio = Carbon::parse($request->fecha_inicio);
        $fechaFin = Carbon::parse($request->fecha_fin);
        $cantidad = $request->cantidad;

        // Comprobar la disponibilidad para cada día en el rango
        $fechas = [];
        $disponible = true;

        for ($fecha = $fechaInicio; $fecha->lte($fechaFin); $fecha->addDay()) {
            $disponibilidad = Disponibilidad::where('habitacion_id', $habitacionId)
                ->where('fecha', $fecha->format('Y-m-d'))
                ->first();

            if (!$disponibilidad || ($disponibilidad->disponibles - $disponibilidad->reservadas) < $cantidad) {
                $disponible = false;
                $fechas[] = [
                    'fecha' => $fecha->format('Y-m-d'),
                    'disponible' => false
                ];
            } else {
                $fechas[] = [
                    'fecha' => $fecha->format('Y-m-d'),
                    'disponible' => true,
                    'disponibles' => $disponibilidad->disponibles - $disponibilidad->reservadas,
                    'precio' => $disponibilidad->precio ?? Habitacion::find($habitacionId)->precio
                ];
            }
        }

        return response()->json([
            'disponible' => $disponible,
            'fechas' => $fechas
        ]);
    }
}