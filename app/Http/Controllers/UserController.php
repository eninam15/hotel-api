<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::with('roles')->get();
        return response()->json($users);
    }

    public function show($id): JsonResponse
    {
        $user = User::with(['roles', 'operadorHoteles.hotel', 'administradorHoteles.operador.hotel'])->findOrFail($id);
        return response()->json($user);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'fec_nacimiento' => 'nullable|date',
            'pais' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'foto_perfil' => 'nullable|image|max:2048',
            'direccion' => 'nullable|string|max:255',
            'sexo' => 'nullable|in:M,F,O',
            'roles' => 'sometimes|array',
            'roles.*' => 'exists:roles,name'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except('foto_perfil', 'password', 'password_confirmation', 'roles');
        $data['password'] = Hash::make($request->password);
        
        // Manejar la carga de imagen
        if ($request->hasFile('foto_perfil')) {
            $path = $request->file('foto_perfil')->store('perfiles', 'public');
            $data['foto_perfil'] = $path;
        }

        $user = User::create($data);

        // Asignar roles
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        } else {
            $user->assignRole('user'); // Rol por defecto
        }

        return response()->json($user->load('roles'), 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'fec_nacimiento' => 'nullable|date',
            'pais' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'foto_perfil' => 'nullable|image|max:2048',
            'direccion' => 'nullable|string|max:255',
            'sexo' => 'nullable|in:M,F,O',
            'roles' => 'sometimes|array',
            'roles.*' => 'exists:roles,name'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except('foto_perfil', 'password', 'password_confirmation', 'roles');
        
        // Actualizar contraseña si se proporciona
        if ($request->has('password') && $request->password) {
            $data['password'] = Hash::make($request->password);
        }
        
        // Manejar la actualización de imagen
        if ($request->hasFile('foto_perfil')) {
            // Eliminar imagen anterior si existe
            if ($user->foto_perfil) {
                Storage::disk('public')->delete($user->foto_perfil);
            }
            
            $path = $request->file('foto_perfil')->store('perfiles', 'public');
            $data['foto_perfil'] = $path;
        }

        $user->update($data);

        // Actualizar roles si se proporcionan
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return response()->json($user->load('roles'));
    }

    public function destroy($id): JsonResponse
    {
        $user = User::findOrFail($id);
        
        // Eliminar imagen si existe
        if ($user->foto_perfil) {
            Storage::disk('public')->delete($user->foto_perfil);
        }
        
        $user->delete();
        
        return response()->json(null, 204);
    }

    public function profile(): JsonResponse
    {
        $user = auth()->user();
        return response()->json($user->load(['roles', 'operadorHoteles.hotel', 'administradorHoteles.operador.hotel']));
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'fec_nacimiento' => 'nullable|date',
            'pais' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'foto_perfil' => 'nullable|image|max:2048',
            'direccion' => 'nullable|string|max:255',
            'sexo' => 'nullable|in:M,F,O'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except('foto_perfil', 'password', 'password_confirmation');
        
        // Actualizar contraseña si se proporciona
        if ($request->has('password') && $request->password) {
            $data['password'] = Hash::make($request->password);
        }
        
        // Manejar la actualización de imagen
        if ($request->hasFile('foto_perfil')) {
            // Eliminar imagen anterior si existe
            if ($user->foto_perfil) {
                Storage::disk('public')->delete($user->foto_perfil);
            }
            
            $path = $request->file('foto_perfil')->store('perfiles', 'public');
            $data['foto_perfil'] = $path;
        }

        $user->update($data);

        return response()->json($user);
    }

    public function assignRole(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|exists:roles,name'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::findOrFail($id);
        $user->assignRole($request->role);
        
        return response()->json($user->load('roles'));
    }

    public function removeRole(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|exists:roles,name'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::findOrFail($id);
        $user->removeRole($request->role);
        
        return response()->json($user->load('roles'));
    }
}