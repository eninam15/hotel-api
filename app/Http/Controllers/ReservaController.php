<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\DetalleReserva;
use App\Models\Disponibilidad;
use App\Models\Habitacion;
use App\Models\Hotel;
use App\Models\Ranking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ReservaController extends Controller
{
    public function index(): JsonResponse
    {
        $user = Auth::user();
        
        if ($user->hasRole('super-admin') || $user->hasRole('admin')) {
            $reservas = Reserva::with(['user', 'hotel', 'detalles.habitacion'])->get();
        } else {
            $reservas = Reserva::with(['hotel', 'detalles.habitacion'])
                ->where('user_id', $user->id)
                ->get();
        }
        
        return response()->json($reservas);
    }

    public function show($id): JsonResponse
    {
        $user = Auth::user();
        $reserva = Reserva::with(['user', 'hotel', 'detalles.habitacion'])->findOrFail($id);
        
        // Verificar permisos: solo el propietario, admin o super-admin pueden ver
        if ($reserva->user_id !== $user->id && !$user->hasRole(['admin', 'super-admin'])) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        
        return response()->json($reserva);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'hotel_id' => 'required|exists:hoteles,id',
            'fec_checkin' => 'required|date|after_or_equal:today',
            'fec_checkout' => 'required|date|after:fec_checkin',
            'detalles' => 'required|array|min:1',
            'detalles.*.habitacion_id' => 'required|exists:habitaciones,id',
            'detalles.*.cantidad' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $checkin = Carbon::parse($request->fec_checkin);
        $checkout = Carbon::parse($request->fec_checkout);
        $nroNoches = $checkout->diffInDays($checkin);
        
        // Verificar disponibilidad para todas las habitaciones solicitadas
        $detalles = $request->detalles;
        $precioTotal = 0;
        $fechasEstadia = [];
        
        // Generar arreglo de fechas de estadía (sin incluir checkout)
        for ($fecha = clone $checkin; $fecha->lt($checkout); $fecha->addDay()) {
            $fechasEstadia[] = $fecha->format('Y-m-d');
        }
        
        // Iniciar transacción para asegurar integridad
        DB::beginTransaction();
        
        try {
            // Verificar disponibilidad y calcular precio para cada habitación
            foreach ($detalles as $detalle) {
                $habitacion = Habitacion::findOrFail($detalle['habitacion_id']);
                
                // Verificar que la habitación pertenezca al hotel
                if ($habitacion->hotel_id != $request->hotel_id) {
                    throw new \Exception("La habitación no pertenece al hotel seleccionado");
                }
                
                $precioHabitacion = 0;
                
                // Verificar disponibilidad para cada fecha
                foreach ($fechasEstadia as $fecha) {
                    $disponibilidad = Disponibilidad::where('habitacion_id', $habitacion->id)
                        ->where('fecha', $fecha)
                        ->first();
                    
                    if (!$disponibilidad || ($disponibilidad->disponibles - $disponibilidad->reservadas) < $detalle['cantidad']) {
                        throw new \Exception("No hay disponibilidad suficiente para la habitación {$habitacion->nombre} en la fecha {$fecha}");
                    }
                    
                    // Actualizar disponibilidad
                    $disponibilidad->reservadas += $detalle['cantidad'];
                    $disponibilidad->save();
                    
                    // Agregar al precio total
                    $precioDia = $disponibilidad->precio ?? $habitacion->precio;
                    $precioHabitacion += $precioDia * $detalle['cantidad'];
                }
                
                $precioTotal += $precioHabitacion;
            }
            
            // Crear la reserva
            $reserva = Reserva::create([
                'nro_reserva' => 'RES-' . Str::random(8),
                'user_id' => $user->id,
                'hotel_id' => $request->hotel_id,
                'nro_noches' => $nroNoches,
                'precio_total' => $precioTotal,
                'estado' => 'pendiente',
                'fec_checkin' => $request->fec_checkin,
                'fec_checkout' => $request->fec_checkout
            ]);
            
            // Crear los detalles de la reserva
            foreach ($detalles as $detalle) {
                $habitacion = Habitacion::findOrFail($detalle['habitacion_id']);
                
                foreach ($fechasEstadia as $fecha) {
                    $disponibilidad = Disponibilidad::where('habitacion_id', $habitacion->id)
                        ->where('fecha', $fecha)
                        ->first();
                    
                    $precioDia = $disponibilidad->precio ?? $habitacion->precio;
                    
                    DetalleReserva::create([
                        'reserva_id' => $reserva->id,
                        'habitacion_id' => $habitacion->id,
                        'cantidad' => $detalle['cantidad'],
                        'precio_hab' => $precioDia,
                        'fecha' => $fecha
                    ]);
                }
            }
            
            // Actualizar contadores para el ranking del hotel
            $ranking = Ranking::where('hotel_id', $request->hotel_id)->first();
            if ($ranking) {
                $ranking->increment('nro_reservas');
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Reserva creada con éxito',
                'reserva' => $reserva->load(['hotel', 'detalles.habitacion'])
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $reserva = Reserva::findOrFail($id);
        $user = Auth::user();
        
        // Verificar permisos: solo el propietario, admin o super-admin pueden actualizar
        if ($reserva->user_id !== $user->id && !$user->hasRole(['admin', 'super-admin'])) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'estado' => 'required|in:pendiente,confirmada,cancelada,completada'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Solo permitir ciertos cambios de estado
        $estadoActual = $reserva->estado;
        $nuevoEstado = $request->estado;
        
        $cambiosPermitidos = [
            'pendiente' => ['confirmada', 'cancelada'],
            'confirmada' => ['completada', 'cancelada'],
            'cancelada' => [],
            'completada' => []
        ];
        
        if (!in_array($nuevoEstado, $cambiosPermitidos[$estadoActual])) {
            return response()->json([
                'message' => "No se puede cambiar el estado de {$estadoActual} a {$nuevoEstado}"
            ], 400);
        }
        
        // Si se cancela la reserva, liberar la disponibilidad
        if ($nuevoEstado === 'cancelada') {
            DB::beginTransaction();
            
            try {
                foreach ($reserva->detalles as $detalle) {
                    $disponibilidad = Disponibilidad::where('habitacion_id', $detalle->habitacion_id)
                        ->where('fecha', $detalle->fecha)
                        ->first();
                    
                    if ($disponibilidad) {
                        $disponibilidad->reservadas -= $detalle->cantidad;
                        $disponibilidad->save();
                    }
                }
                
                $reserva->update(['estado' => $nuevoEstado]);
                DB::commit();
                
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['message' => $e->getMessage()], 400);
            }
        } else {
            $reserva->update(['estado' => $nuevoEstado]);
        }
        
        return response()->json([
            'message' => 'Estado de reserva actualizado',
            'reserva' => $reserva->load(['hotel', 'detalles.habitacion'])
        ]);
    }

    public function misReservas(): JsonResponse
    {
        $user = Auth::user();
        $reservas = Reserva::with(['hotel', 'detalles.habitacion'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($reservas);
    }

    public function reservasPorHotel($hotelId): JsonResponse
    {
        $user = Auth::user();
        $hotel = Hotel::findOrFail($hotelId);
        
        // Verificar si el usuario es operador o administrador del hotel
        $esOperador = $user->operadorHoteles()->where('hotel_id', $hotelId)->exists();
        $esAdmin = $user->hasRole(['admin', 'super-admin']);
        
        if (!$esOperador && !$esAdmin) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        
        $reservas = Reserva::with(['user', 'detalles.habitacion'])
            ->where('hotel_id', $hotelId)
            ->orderBy('fec_checkin')
            ->get();
        
        return response()->json($reservas);
    }
}