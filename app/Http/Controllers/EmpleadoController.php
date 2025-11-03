<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmpleadoController extends Controller
{
    /**
     * Listar todos los empleados
     */
    public function index()
    {
        $empleados = Empleado::with(['cargo', 'dependencia'])->get();

        return response()->json([
            'success' => true,
            'data' => $empleados
        ], 200);
    }

    /**
     * Crear un nuevo empleado
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:120',
            'apellido' => 'required|string|max:120',
            'email' => 'required|email|max:150|unique:empleado,email',
            'telefono' => 'nullable|string|max:30',
            'id_cargo' => 'required|exists:cargo,id_cargo',
            'id_dependencia' => 'required|exists:dependencia,id_dependencia',
            'estado' => 'boolean'
        ], [
            'nombre.required' => 'El nombre del empleado es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 120 caracteres',
            'apellido.required' => 'El apellido del empleado es obligatorio',
            'apellido.max' => 'El apellido no puede exceder 120 caracteres',
            'email.required' => 'El email electrónico es obligatorio',
            'email.email' => 'El email electrónico debe ser válido',
            'email.max' => 'El email no puede exceder 150 caracteres',
            'email.unique' => 'Este email electrónico ya está registrado',
            'telefono.max' => 'El teléfono no puede exceder 30 caracteres',
            'id_cargo.required' => 'El cargo es obligatorio',
            'id_cargo.exists' => 'El cargo seleccionado no existe',
            'id_dependencia.required' => 'La dependencia es obligatoria',
            'id_dependencia.exists' => 'La dependencia seleccionada no existe',
            'estado.boolean' => 'El estado debe ser verdadero o falso'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $empleado = Empleado::create($request->all());
        $empleado->load(['cargo', 'dependencia']);

        return response()->json([
            'success' => true,
            'message' => 'Empleado creado exitosamente',
            'data' => $empleado
        ], 201);
    }

    /**
     * Mostrar un empleado específico
     */
    public function show(string $id)
    {
        $empleado = Empleado::with(['cargo', 'dependencia'])->find($id);

        if (!$empleado) {
            return response()->json([
                'success' => false,
                'message' => 'Empleado no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $empleado
        ], 200);
    }

    /**
     * Actualizar un empleado
     */
    public function update(Request $request, string $id)
    {
        $empleado = Empleado::find($id);

        if (!$empleado) {
            return response()->json([
                'success' => false,
                'message' => 'Empleado no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:120',
            'apellido' => 'required|string|max:120',
            'email' => 'required|email|max:150|unique:empleado,email,' . $id . ',id_empleado',
            'telefono' => 'nullable|string|max:30',
            'id_cargo' => 'required|exists:cargo,id_cargo',
            'id_dependencia' => 'required|exists:dependencia,id_dependencia',
            'estado' => 'boolean'
        ], [
            'nombre.required' => 'El nombre del empleado es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 120 caracteres',
            'apellido.required' => 'El apellido del empleado es obligatorio',
            'apellido.max' => 'El apellido no puede exceder 120 caracteres',
            'email.required' => 'El email electrónico es obligatorio',
            'email.email' => 'El email electrónico debe ser válido',
            'email.max' => 'El email no puede exceder 150 caracteres',
            'email.unique' => 'Este email electrónico ya está registrado',
            'telefono.max' => 'El teléfono no puede exceder 30 caracteres',
            'id_cargo.required' => 'El cargo es obligatorio',
            'id_cargo.exists' => 'El cargo seleccionado no existe',
            'id_dependencia.required' => 'La dependencia es obligatoria',
            'id_dependencia.exists' => 'La dependencia seleccionada no existe',
            'estado.boolean' => 'El estado debe ser verdadero o falso'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $empleado->update($request->all());
        $empleado->load(['cargo', 'dependencia']);

        return response()->json([
            'success' => true,
            'message' => 'Empleado actualizado exitosamente',
            'data' => $empleado
        ], 200);
    }

    /**
     * Eliminar un empleado
     */
    public function destroy(string $id)
    {
        $empleado = Empleado::find($id);

        if (!$empleado) {
            return response()->json([
                'success' => false,
                'message' => 'Empleado no encontrado'
            ], 404);
        }

        $empleado->delete();

        return response()->json([
            'success' => true,
            'message' => 'Empleado eliminado exitosamente'
        ], 200);
    }

    /**
     * Listar empleados activos
     */
    public function activos()
    {
        $empleados = Empleado::where('estado', true)
            ->with(['cargo', 'dependencia'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $empleados
        ], 200);
    }

    /**
     * Listar empleados inactivos
     */
    public function inactivos()
    {
        $empleados = Empleado::where('estado', false)
            ->with(['cargo', 'dependencia'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $empleados
        ], 200);
    }

    /**
     * Cambiar estado de un empleado
     */
    public function cambiarEstado(string $id)
    {
        $empleado = Empleado::find($id);

        if (!$empleado) {
            return response()->json([
                'success' => false,
                'message' => 'Empleado no encontrado'
            ], 404);
        }

        $empleado->estado = !$empleado->estado;
        $empleado->save();
        $empleado->load(['cargo', 'dependencia']);

        return response()->json([
            'success' => true,
            'message' => 'Estado del empleado actualizado exitosamente',
            'data' => $empleado
        ], 200);
    }

    /**
     * Listar empleados por cargo
     */
    public function byCargo(string $id_cargo)
    {
        $empleados = Empleado::where('id_cargo', $id_cargo)
            ->with(['cargo', 'dependencia'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $empleados
        ], 200);
    }

    /**
     * Listar empleados por dependencia
     */
    public function byDependencia(string $id_dependencia)
    {
        $empleados = Empleado::where('id_dependencia', $id_dependencia)
            ->with(['cargo', 'dependencia'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $empleados
        ], 200);
    }

    /**
     * Buscar empleados por nombre o apellido
     */
    public function buscar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'termino' => 'required|string|min:2'
        ], [
            'termino.required' => 'El término de búsqueda es obligatorio',
            'termino.min' => 'El término de búsqueda debe tener al menos 2 caracteres'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $termino = $request->termino;

        $empleados = Empleado::where(function ($query) use ($termino) {
            $query->where('nombre', 'ILIKE', '%' . $termino . '%')
                  ->orWhere('apellido', 'ILIKE', '%' . $termino . '%')
                  ->orWhere('email', 'ILIKE', '%' . $termino . '%');
        })
        ->with(['cargo', 'dependencia'])
        ->get();

        if ($empleados->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron empleados con ese término de búsqueda'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $empleados
        ], 200);
    }

    /**
     * Obtener estadísticas de empleados
     */
    public function estadisticas()
    {
        $totalEmpleados = Empleado::count();
        $empleadosActivos = Empleado::where('estado', true)->count();
        $empleadosInactivos = Empleado::where('estado', false)->count();

        $empleadosPorCargo = Empleado::selectRaw('id_cargo, COUNT(*) as total')
            ->with('cargo:id_cargo,nombre')
            ->groupBy('id_cargo')
            ->get()
            ->map(function ($item) {
                return [
                    'cargo' => $item->cargo->nombre,
                    'total' => $item->total
                ];
            });

        $empleadosPorDependencia = Empleado::selectRaw('id_dependencia, COUNT(*) as total')
            ->with('dependencia:id_dependencia,nombre')
            ->groupBy('id_dependencia')
            ->get()
            ->map(function ($item) {
                return [
                    'dependencia' => $item->dependencia->nombre,
                    'total' => $item->total
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'total_empleados' => $totalEmpleados,
                'empleados_activos' => $empleadosActivos,
                'empleados_inactivos' => $empleadosInactivos,
                'por_cargo' => $empleadosPorCargo,
                'por_dependencia' => $empleadosPorDependencia
            ]
        ], 200);
    }
}