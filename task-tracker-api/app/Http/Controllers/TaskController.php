<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function allData()
    {
        // Verificamos que sea admin
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $allTasks = \App\Models\Task::with('user')->get();

        return response()->json([
            'total_users' => \App\Models\User::count(),
            'total_tasks' => $allTasks->count(), // Contamos la colección obtenida
            'users' => \App\Models\User::all(),
            'all_tasks' => $allTasks
        ]);
    }

    public function index()
    {
        $tasks = Auth::user()->tasks;
        return response()->json($tasks, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'string',
            'priority' => 'string',
            'user_id' => 'nullable|integer' // Campo opcional para asignar a otros
        ]);

        // Si es admin y envió un user_id, usamos ese. Si no, usamos su propio ID.
        if (auth()->user()->role === 'admin' && $request->has('user_id')) {
            $userId = $request->user_id;
        } else {
            $userId = auth()->id();
        }

        $task = \App\Models\Task::create(array_merge($data, ['user_id' => $userId]));
        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        // Validar que la tarea sea del usuario
        if ($task->user_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        return response()->json($task, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        // Permitir si es el dueño O si es admin
        if ($task->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $task->update($request->all());
        return response()->json($task);
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);

        // 1. Verificar si existe una ruta de evidencia guardada
        if ($task->evidence_path) {
            // 2. Eliminar el archivo del disco 'public'
            // Esto busca en storage/app/public/ la ruta guardada
            Storage::disk('public')->delete($task->evidence_path);
        }

        // 3. Eliminar el registro de la base de datos
        $task->delete();

        return response()->json(['message' => 'Tarea y evidencia física eliminadas correctamente']);
    }

    public function updateProgress(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        // Seguridad: Solo el dueño de la tarea puede actualizar su progreso
        if ($task->user_id !== auth()->id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Validamos los datos (la evidencia es opcional pero recomendada)
        $request->validate([
            'status' => 'required|string|in:pending,in_progress,completed',
            'employee_notes' => 'nullable|string',
            'evidence' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // Max 2MB
        ]);

        // Lógica de tiempo: Si pasa a "En proceso" por primera vez, grabamos la hora
        if ($request->status === 'in_progress' && !$task->started_at) {
            $task->started_at = now();
        }

        // Manejo de la imagen (Evidencia)
        if ($request->hasFile('evidence')) {
            // Guardamos la imagen en storage/app/public/evidences
            $path = $request->file('evidence')->store('evidences', 'public');
            $task->evidence_path = $path;
        }

        // Actualizamos el resto de campos
        $task->status = $request->status;
        $task->employee_notes = $request->employee_notes;
        $task->save();

        return response()->json([
            'message' => 'Progreso actualizado',
            'task' => $task
        ], 200);
    }
}
