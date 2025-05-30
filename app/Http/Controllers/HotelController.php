<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Ranking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class HotelController extends Controller
{
    public function index(): JsonResponse
    {
        $hoteles = Hotel::with(['servicios', 'ranking'])->get();
        return response()->json($hoteles);
    }

    public function show($id): JsonResponse
    {
        $hotel = Hotel::with(['servicios', 'habitaciones', 'ranking'])->findOrFail($id);
        
        // Incrementar contador de visitas en el ranking
        if ($hotel->ranking) {
            $hotel->ranking->increment('visitas');
        }
        
        return response()->json($hotel);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'telefono' => 'required|string|max:255',
            'foto' => 'nullable|image|max:2048',
            'ciudad' => 'required|string|max:255',
            'hr_entrada' => 'nullable|date_format:H:i',
            'hr_salida' => 'nullable|date_format:H:i',
            'publicar' => 'boolean',
            'servicios' => 'nullable|array',
            'servicios.*' => 'exists:servicios,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except('foto', 'servicios');
        
        // Manejar la carga de imagen
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('hoteles', 'public');
            $data['foto'] = $path;
        }

        $hotel = Hotel::create($data);

        // Sincronizar servicios si se proporcionan
        if ($request->has('servicios')) {
            $hotel->servicios()->sync($request->servicios);
        }

        // Crear un registro de ranking vacío para el hotel
        Ranking::create([
            'hotel_id' => $hotel->id,
            'pt_general' => 0,
            'nro_valoraciones' => 0,
            'nro_reservas' => 0,
            'visitas' => 0
        ]);

        return response()->json($hotel, 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $hotel = Hotel::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:255',
            'categoria' => 'sometimes|required|string|max:255',
            'direccion' => 'sometimes|required|string|max:255',
            'telefono' => 'sometimes|required|string|max:255',
            'foto' => 'nullable|image|max:2048',
            'ciudad' => 'sometimes|required|string|max:255',
            'hr_entrada' => 'nullable|date_format:H:i',
            'hr_salida' => 'nullable|date_format:H:i',
            'publicar' => 'boolean',
            'servicios' => 'nullable|array',
            'servicios.*' => 'exists:servicios,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except('foto', 'servicios');
        
        // Manejar la actualización de imagen
        if ($request->hasFile('foto')) {
            // Eliminar imagen anterior si existe
            if ($hotel->foto) {
                Storage::disk('public')->delete($hotel->foto);
            }
            
            $path = $request->file('foto')->store('hoteles', 'public');
            $data['foto'] = $path;
        }

        $hotel->update($data);

        // Sincronizar servicios si se proporcionan
        if ($request->has('servicios')) {
            $hotel->servicios()->sync($request->servicios);
        }

        return response()->json($hotel);
    }

    public function destroy($id): JsonResponse
    {
        $hotel = Hotel::findOrFail($id);
        
        // Eliminar imagen si existe
        if ($hotel->foto) {
            Storage::disk('public')->delete($hotel->foto);
        }
        
        $hotel->delete();
        
        return response()->json(null, 204);
    }

    public function buscar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ciudad' => 'nullable|string',
            'fecha_entrada' => 'nullable|date',
            'fecha_salida' => 'nullable|date|after_or_equal:fecha_entrada',
            'adultos' => 'nullable|integer|min:1',
            'ninos' => 'nullable|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $query = Hotel::with(['servicios', 'ranking'])
            ->where('publicar', true);

        // Filtrar por ciudad si se proporciona
        if ($request->has('ciudad')) {
            $query->where('ciudad', 'like', '%' . $request->ciudad . '%');
        }

        $hoteles = $query->get();

        // Si se proporcionan fechas, filtrar hoteles con disponibilidad
        if ($request->has('fecha_entrada') && $request->has('fecha_salida')) {
            $fechaEntrada = $request->fecha_entrada;
            $fechaSalida = $request->fecha_salida;
            $adultos = $request->adultos ?? 1;
            $ninos = $request->ninos ?? 0;

            $hotelesConDisponibilidad = [];

            foreach ($hoteles as $hotel) {
                $habitacionesDisponibles = $hotel->habitaciones()
                    ->where('nro_adultos', '>=', $adultos)
                    ->where('nro_ninos', '>=', $ninos)
                    ->whereHas('disponibilidades', function ($query) use ($fechaEntrada, $fechaSalida) {
                        $query->whereBetween('fecha', [$fechaEntrada, $fechaSalida])
                              ->whereRaw('disponibles > reservadas');
                    })
                    ->count();

                if ($habitacionesDisponibles > 0) {
                    $hotelesConDisponibilidad[] = $hotel;
                }
            }

            return response()->json($hotelesConDisponibilidad);
        }

        return response()->json($hoteles);
    }
}