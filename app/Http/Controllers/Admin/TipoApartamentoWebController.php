<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoApartamento;
use App\Models\Proyecto;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Support\RedirectBackTo;

class TipoApartamentoWebController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo');
        $tipos = TipoApartamento::with(['proyecto'])
            ->withCount('apartamentos')
            ->orderBy('id_tipo_apartamento', 'desc')
            ->get()
            ->map(fn($t) => [
                'id_tipo_apartamento' => $t->id_tipo_apartamento,
                'nombre' => $t->nombre,
                'area_construida' => $t->area_construida,
                'area_privada' => $t->area_privada,
                'cantidad_habitaciones' => $t->cantidad_habitaciones,
                'cantidad_banos' => $t->cantidad_banos,
                'valor_m2' => $t->valor_m2,
                'valor_estimado' => $t->valor_estimado,
                'apartamentos_count' => $t->apartamentos_count,
                'proyecto' => $t->proyecto?->nombre,
            ]);

        return Inertia::render('Admin/TipoApartamento/Index', [
            'tipos' => $tipos,
            'empleado' => $empleado,
        ]);
    }

    public function create(Request $request)
    {
        $empleado = $request->user()->load('cargo');
        $proyectos = Proyecto::orderBy('nombre')->get();

        return Inertia::render('Admin/TipoApartamento/Create', [
            'proyectos' => $proyectos,
            'empleado' => $empleado,
        ]);
    }

    public function store(Request $request)
    {
        // ✅ MODO LOTE
        if ($request->has('tipos') && is_array($request->input('tipos'))) {

            $validated = $request->validate([
                'id_proyecto' => 'required|exists:proyectos,id_proyecto',
                'tipos' => 'required|array|min:1',
                'tipos.*.nombre' => 'required|string|max:100',
                'tipos.*.area_construida' => 'nullable|numeric|min:0|max:99999999.99',
                'tipos.*.area_privada' => 'nullable|numeric|min:0|max:99999999.99',
                'tipos.*.cantidad_habitaciones' => 'nullable|integer|min:0|max:32767',
                'tipos.*.cantidad_banos' => 'nullable|integer|min:0|max:32767',
                'tipos.*.valor_m2' => 'nullable|numeric|min:0|max:9999999999999999.99',
                'tipos.*.imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);

            // ✅ aquí vienen los archivos anidados de forma confiable
            $tiposFiles = $request->file('tipos') ?? [];

            \DB::transaction(function () use ($validated, $tiposFiles) {

                foreach ($validated['tipos'] as $i => $row) {

                    // Imagen (robusto)
                    if (!empty($tiposFiles[$i]['imagen'])) {
                        $ruta = $tiposFiles[$i]['imagen']->store('tipos-apartamento', 'public');
                        $row['imagen'] = $ruta;
                    }

                    // Calcular valor_estimado
                    $area = (float)($row['area_construida'] ?? 0);
                    $valorM2 = (float)($row['valor_m2'] ?? 0);

                    $row['valor_estimado'] = ($area > 0 && $valorM2 > 0)
                        ? ceil($area * $valorM2)
                        : null;

                    $row['id_proyecto'] = $validated['id_proyecto'];

                    // Limpieza opcional si llegan campos extra desde frontend
                    unset($row['_key'], $row['_fileName']);

                    TipoApartamento::create($row);
                }
            });

            return RedirectBackTo::respond(
                $request,
                'apartamentos.create',
                [],
                'Tipos de apartamento creados exitosamente'
            );
        }

        // ✅ MODO SIMPLE: mantiene tu implementación actual
        $validated = $request->validate([
            'id_proyecto' => 'required|exists:proyectos,id_proyecto',
            'nombre' => 'required|string|max:100',
            'area_construida' => 'nullable|numeric|min:0|max:99999999.99',
            'area_privada' => 'nullable|numeric|min:0|max:99999999.99',
            'cantidad_habitaciones' => 'nullable|integer|min:0|max:32767',
            'cantidad_banos' => 'nullable|integer|min:0|max:32767',
            'valor_m2' => 'nullable|numeric|min:0|max:9999999999999999.99',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'nombre.required' => 'El nombre del tipo de apartamento es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres',
            'area_construida.numeric' => 'El área construida debe ser un valor numérico',
            'area_construida.min' => 'El área construida no puede ser negativa',
            'area_privada.numeric' => 'El área privada debe ser un valor numérico',
            'area_privada.min' => 'El área privada no puede ser negativa',
            'cantidad_habitaciones.integer' => 'La cantidad de habitaciones debe ser un número entero',
            'cantidad_habitaciones.min' => 'La cantidad de habitaciones no puede ser negativa',
            'cantidad_banos.integer' => 'La cantidad de baños debe ser un número entero',
            'cantidad_banos.min' => 'La cantidad de baños no puede ser negativa',
            'valor_m2.numeric' => 'El valor por m² debe ser un valor numérico',
            'valor_m2.min' => 'El valor por m² no puede ser negativo',
            'imagen.image' => 'El archivo debe ser una imagen válida',
            'imagen.mimes' => 'La imagen debe ser en formato JPG, PNG o WEBP',
            'imagen.max' => 'La imagen no puede pesar más de 2MB',
        ]);

        if ($request->hasFile('imagen')) {
            $ruta = $request->file('imagen')->store('tipos-apartamento', 'public');
            $validated['imagen'] = $ruta;
        }

        $area = (float)($validated['area_construida'] ?? 0);
        $valorM2 = (float)($validated['valor_m2'] ?? 0);
        if ($area > 0 && $valorM2 > 0) {
            $valorCalculado = $area * $valorM2 * 1.08;
            $validated['valor_estimado'] = ceil($valorCalculado);
        } else {
            $validated['valor_estimado'] = null;
        }

        $tipo = TipoApartamento::create($validated);

        return RedirectBackTo::respond(
            $request,
            'tipos-apartamento.show',
            [$tipo->id_tipo_apartamento],
            'Tipo de apartamento creado exitosamente',
            ['id_tipo_apartamento' => $tipo->id_tipo_apartamento]
        );
    }

    public function show(Request $request, $id)
    {
        $empleado = $request->user()->load('cargo');
        $tipo = TipoApartamento::with(['apartamentos.torre', 'apartamentos.estadoInmueble'])
            ->findOrFail($id);

        return Inertia::render('Admin/TipoApartamento/Show', [
            'tipo' => [
                'id_proyecto' => $tipo->proyecto->nombre,
                'id_tipo_apartamento' => $tipo->id_tipo_apartamento,
                'nombre' => $tipo->nombre,
                'area_construida' => $tipo->area_construida,
                'area_privada' => $tipo->area_privada,
                'cantidad_habitaciones' => $tipo->cantidad_habitaciones,
                'cantidad_banos' => $tipo->cantidad_banos,
                'valor_m2' => $tipo->valor_m2,
                'valor_estimado' => $tipo->valor_estimado,
                'imagen' => $tipo->imagen,
            ],
            'apartamentos' => $tipo->apartamentos->map(function ($a) {
                return [
                    'id_apartamento' => $a->id_apartamento,
                    'numero' => $a->numero,
                    'torre' => $a->torre?->nombre_torre,
                    'estado' => $a->estadoInmueble?->nombre,
                ];
            }),
            'empleado' => $empleado,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $empleado = $request->user()->load('cargo');
        $t = TipoApartamento::findOrFail($id);
        $proyectos = Proyecto::orderBy('nombre')->get();

        return Inertia::render('Admin/TipoApartamento/Edit', [
            'tipo' => [
                'id_proyecto' => $t->id_proyecto,
                'id_tipo_apartamento' => $t->id_tipo_apartamento,
                'nombre' => $t->nombre,
                'area_construida' => $t->area_construida,
                'area_privada' => $t->area_privada,
                'cantidad_habitaciones' => $t->cantidad_habitaciones,
                'cantidad_banos' => $t->cantidad_banos,
                'valor_m2' => $t->valor_m2,
                'imagen' => $t->imagen,
            ],
            'proyectos' => $proyectos,
            'empleado' => $empleado,
        ]);
    }

    public function update(Request $request, $id)
    {
        $t = TipoApartamento::findOrFail($id);

        $validated = $request->validate([
            'id_proyecto' => 'required|exists:proyectos,id_proyecto',
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('tipos_apartamento')->ignore($t->id_tipo_apartamento, 'id_tipo_apartamento')
            ],
            'area_construida' => 'nullable|numeric|min:0|max:99999999.99',
            'area_privada' => 'nullable|numeric|min:0|max:99999999.99',
            'cantidad_habitaciones' => 'nullable|integer|min:0|max:32767',
            'cantidad_banos' => 'nullable|integer|min:0|max:32767',
            'valor_m2' => 'nullable|numeric|min:0|max:9999999999999999.99',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'nombre.required' => 'El nombre del tipo de apartamento es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres',
            'area_construida.numeric' => 'El área construida debe ser un valor numérico',
            'area_construida.min' => 'El área construida no puede ser negativa',
            'area_privada.numeric' => 'El área privada debe ser un valor numérico',
            'area_privada.min' => 'El área privada no puede ser negativa',
            'cantidad_habitaciones.integer' => 'La cantidad de habitaciones debe ser un número entero',
            'cantidad_habitaciones.min' => 'La cantidad de habitaciones no puede ser negativa',
            'cantidad_banos.integer' => 'La cantidad de baños debe ser un número entero',
            'cantidad_banos.min' => 'La cantidad de baños no puede ser negativa',
            'valor_m2.numeric' => 'El valor por m² debe ser un valor numérico',
            'valor_m2.min' => 'El valor por m² no puede ser negativo',
            'imagen.image' => 'El archivo debe ser una imagen válida',
            'imagen.mimes' => 'La imagen debe ser en formato JPG, PNG o WEBP',
            'imagen.max' => 'La imagen no puede pesar más de 2MB',
        ]);

        if ($request->hasFile('imagen')) {
            if ($t->imagen) {
                Storage::disk('public')->delete($t->imagen);
            }
            $ruta = $request->file('imagen')->store('tipos-apartamento', 'public');
            $validated['imagen'] = $ruta;
        }

        // Recalcular y persistir valor_estimado
        $area = (float)($validated['area_construida'] ?? 0);
        $valorM2 = (float)($validated['valor_m2'] ?? 0);
        if ($area > 0 && $valorM2 > 0) {
            $valorCalculado = $area * $valorM2;
            $validated['valor_estimado'] = ceil($valorCalculado);
        } else {
            $validated['valor_estimado'] = null;
        }

        $validated['id_proyecto'] = $request->id_proyecto;

        $t->update($validated);

        return redirect()->route('tipos-apartamento.show', $t->id_tipo_apartamento)
            ->with('success', 'Tipo de apartamento actualizado exitosamente');
    }

    public function destroy($id)
    {
        $t = TipoApartamento::withCount('apartamentos')->findOrFail($id);

        if ($t->apartamentos_count > 0) {
            return back()->withErrors(['delete' => 'No se puede eliminar el tipo de apartamento porque tiene apartamentos asociados']);
        }

        $t->delete();

        return redirect()->route('tipos-apartamento.index')->with('success', 'Tipo de apartamento eliminado exitosamente');
    }
}
