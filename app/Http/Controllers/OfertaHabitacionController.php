<?php

namespace App\Http\Controllers;

use App\Models\OfertaHabitacion;
use App\Models\Habitacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class OfertaHabitacionController extends Controller
{
    public function index(): JsonResponse
    {
        $ofertas = OfertaHabitacion::with(['servicio', 'habitacion.hotel'])
            ->orderBy('fec_inicio')
            ->get();
        
        return response()->json($ofertas);
    }

    public function show($id): JsonResponse
    {
        $oferta = OfertaHabitacion::with(['servicio', 'habitacion.hotel'])->findOrFail($id);
        return response()->json($oferta);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'servicio_id' => 'required|exists:servicios,id',
            'habitacion_id' => 'required|exists:habitaciones,id',
            'descuento' => 'required|numeric|min:0|max:100',
            'fec_inicio' => 'required|date|after_or_equal:today',
            'fec_fin' => 'required|date|after_or_equal:fec_inicio'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verificar si ya existe una oferta para la misma habitación y fechas superpuestas
        $existeOferta = OfertaHabitacion::where('habitacion_id', $request->habitacion_id)
            ->where(function($query) use ($request) {
                $query->whereBetween('fec_inicio', [$request->fec_inicio, $request->fec_fin])
                    ->orWhereBetween('fec_fin', [$request->fec_inicio, $request->fec_fin])
                    ->orWhere(function($q) use ($request) {
                        $q->where('fec_inicio', '<=', $request->fec_inicio)
                            ->where('fec_fin', '>=', $request->fec_fin);
                    });
            })
            ->exists();
        
        if ($existeOferta) {
            return response()->json([
                'message' => 'Ya existe una oferta para esta habitación en el rango de fechas seleccionado'
            ], 400);
        }

        $oferta = OfertaHabitacion::create($request->all());
        return response()->json($oferta, 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $oferta = OfertaHabitacion::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'servicio_id' => 'sometimes|required|exists:servicios,id',
            'habitacion_id' => 'sometimes|required|exists:habitaciones,id',
            'descuento' => 'sometimes|required|numeric|min:0|max:100',
            'fec_inicio' => 'sometimes|required|date',
            'fec_fin' => 'sometimes|required|date|after_or_equal:fec_inicio'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Si cambia la habitación o las fechas, verificar superposición
        if (($request->has('habitacion_id') && $request->habitacion_id != $oferta->habitacion_id) ||
            ($request->has('fec_inicio') || $request->has('fec_fin'))) {
            
            $habitacionId = $request->habitacion_id ?? $oferta->habitacion_id;
            $fechaInicio = $request->fec_inicio ?? $oferta->fec_inicio;
            $fechaFin = $request->fec_fin ?? $oferta->fec_fin;
            
            $existeOferta = OfertaHabitacion::where('habitacion_id', $habitacionId)
                ->where('id', '!=', $id)
                ->where(function($query) use ($fechaInicio, $fechaFin) {
                    $query->whereBetween('fec_inicio', [$fechaInicio, $fechaFin])
                        ->orWhereBetween('fec_fin', [$fechaInicio, $fechaFin])
                        ->orWhere(function($q) use ($fechaInicio, $fechaFin) {
                            $q->where('fec_inicio', '<=', $fechaInicio)
                                ->where('fec_fin', '>=', $fechaFin);
                        });
                })
                ->exists();
            
            if ($existeOferta) {
                return response()->json([
                    'message' => 'Ya existe una oferta para esta habitación en el rango de fechas seleccionado'
                ], 400);
            }
        }

        $oferta->update($request->all());
        return response()->json($oferta);
    }

    public function destroy($id): JsonResponse
    {
        $oferta = OfertaHabitacion::findOrFail($id);
        $oferta->delete();
        
        return response()->json(null, 204);
    }

    public function ofertasPorHotel($hotelId): JsonResponse
    {
        $ofertas = OfertaHabitacion::with(['servicio', 'habitacion'])
            ->whereHas('habitacion', function($query) use ($hotelId) {
                $query->where('hotel_id', $hotelId);
            })
            ->where('fec_fin', '>=', Carbon::today())
            ->orderBy('fec_inicio')
            ->get();
        
        return response()->json($ofertas);
    }
}