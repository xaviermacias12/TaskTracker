<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Cargamos los usuarios con sus tokens para ver la actividad
        $users = User::with('tokens')->get()->map(function ($user) {
            // Obtenemos el último token usado
            $lastToken = $user->tokens->sortByDesc('last_used_at')->first();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'last_login' => $lastToken && $lastToken->last_used_at
                    ? $lastToken->last_used_at->diffForHumans()
                    : 'Nunca'
            ];
        });

        return response()->json($users, 200);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function store(Request $request)
    {
        // Solo el admin puede crear usuarios
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:admin,user'
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        return response()->json([
            'message' => 'Usuario creado correctamente por el administrador',
            'user' => $user
        ], 201);
    }

    /**
     * Actualizar un usuario (Nombre, Email, Rol)
     */
    public function update(Request $request, $id)
    {
        // Solo el admin puede editar usuarios
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $user = User::findOrFail($id);

        $data = $request->validate([
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'string|in:admin,user',
            'password' => 'nullable|string|min:6'
        ]);

        // Si se envió contraseña, la encriptamos
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'user' => $user
        ]);
    }

    /**
     * Permite que el usuario (empleado o admin) edite su propio perfil desde la App
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user(); // Identificamos al usuario por su token

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6'
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Perfil actualizado con éxito',
            'user' => $user
        ], 200);
    }

    /**
     * Eliminar un usuario
     */
    public function destroy($id)
    {
        // Solo el admin puede eliminar
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $user = User::findOrFail($id);

        // Evitar que el admin se borre a sí mismo
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'No puedes eliminar tu propia cuenta'], 400);
        }

        $user->delete();

        return response()->json(['message' => 'Usuario eliminado']);
    }
}