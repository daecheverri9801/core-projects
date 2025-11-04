<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\Cargo;
use App\Models\Dependencia;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


class EmpleadoController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo');
        $query = Empleado::with(['cargo', 'dependencia']);

        // Búsqueda por nombre, apellido o email
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'ILIKE', "%$search%")
                  ->orWhere('apellido', 'ILIKE', "%$search%")
                  ->orWhere('email', 'ILIKE', "%$search%");
            });
        }

        $empleados = $query->orderBy('nombre')->paginate(10)->withQueryString();

        return Inertia::render('Admin/Empleados/Index', [
            'empleados' => $empleados,
            'filters' => $request->only('search'),
            'empleado' => $empleado,
        ]);
    }

    public function create(Request $request)
    {
        $empleado = $request->user()->load('cargo');
        $cargos = Cargo::orderBy('nombre')->get();
        $dependencias = Dependencia::orderBy('nombre')->get();

        return Inertia::render('Admin/Empleados/Create', [
            'cargos' => $cargos,
            'dependencias' => $dependencias,
            'empleado' => $empleado,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:120',
            'apellido' => 'required|string|max:120',
            'email' => 'required|email|max:150|unique:empleados,email',
            'password' => 'required|string|min:6',
            'telefono' => 'nullable|string|max:30',
            'id_cargo' => 'required|exists:cargos,id_cargo',
            'id_dependencia' => 'required|exists:dependencias,id_dependencia',
            'estado' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        Empleado::create($validated);

        return redirect()->route('empleados.index')->with('success', 'Empleado creado exitosamente');
    }

    public function show($id, Request $request)
    {
        $empleado = $request->user()->load('cargo');
        $empleado = Empleado::with(['cargo', 'dependencia'])->findOrFail($id);

        return Inertia::render('Admin/Empleados/View', [
            'empleado' => $empleado,
        ]);
    }

    public function edit($id, Request $request)
    {
        $empleado = $request->user()->load('cargo');
        $empleado = Empleado::findOrFail($id);
        $cargos = Cargo::orderBy('nombre')->get();
        $dependencias = Dependencia::orderBy('nombre')->get();

        return Inertia::render('Admin/Empleados/Edit', [
            'empleado' => $empleado,
            'cargos' => $cargos,
            'dependencias' => $dependencias,
        ]);
    }

    public function update(Request $request, $id)
    {
        $empleado = Empleado::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:120',
            'apellido' => 'required|string|max:120',
            'email' => ['required', 'email', 'max:150', Rule::unique('empleados', 'email')->ignore($empleado->id_empleado, 'id_empleado')],
            'telefono' => 'nullable|string|max:30',
            'id_cargo' => 'required|exists:cargos,id_cargo',
            'id_dependencia' => 'required|exists:dependencias,id_dependencia',
            'estado' => 'boolean',
            'password' => 'nullable|string|min:6',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $empleado->update($validated);

        return redirect()->route('empleados.index')->with('success', 'Empleado actualizado exitosamente');
    }

    public function destroy($id)
    {
        $empleado = Empleado::findOrFail($id);
        $empleado->delete();

        return redirect()->route('empleados.index')->with('success', 'Empleado eliminado exitosamente');
    }
}