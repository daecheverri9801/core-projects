<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Torre;
use App\Models\Proyecto;
use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Support\RedirectBackTo;
use Illuminate\Support\Facades\DB;

class AdminTorreController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo');
        $search = $request->get('search');

        /**
         * Ahora el index pagina por PROYECTO y carga sus TORRES.
         * Si hay búsqueda, filtra proyectos que tengan torres cuyo nombre coincida.
         */
        $proyectosQuery = Proyecto::query()
            ->select('id_proyecto', 'nombre')
            ->with([
                'torres' => function ($q) use ($search) {
                    $q->select('id_torre', 'nombre_torre', 'numero_pisos', 'id_proyecto', 'id_estado', 'nivel_inicio_prima')
                        ->with(['estado:id_estado,nombre'])
                        ->when($search, fn($qq) => $qq->where('nombre_torre', 'ILIKE', '%' . $search . '%'))
                        ->orderBy('id_torre', 'desc');
                }
            ])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('torres', fn($t) => $t->where('nombre_torre', 'ILIKE', '%' . $search . '%'));
            })
            ->orderBy('nombre');

        $proyectos = $proyectosQuery->paginate(10)->withQueryString();

        return Inertia::render('Admin/Torres/Index', [
            'proyectos' => $proyectos,
            'filters' => ['search' => $search],
            'empleado' => $empleado,
        ]);
    }

    public function create(Request $request)
    {
        $empleado = $request->user()->load('cargo');
        $proyectos = Proyecto::select('id_proyecto', 'nombre')->orderBy('nombre')->get();
        $estados = Estado::select('id_estado', 'nombre')->orderBy('nombre')->get();

        return Inertia::render('Admin/Torres/Create', [
            'proyectos' => $proyectos,
            'estados' => $estados,
            'empleado' => $empleado,
        ]);
    }

    public function store(Request $request)
    {
        $isBulk = $request->has('torres') && is_array($request->input('torres'));

        if ($isBulk) {
            $validated = $request->validate([
                'id_proyecto' => ['required', Rule::exists('proyectos', 'id_proyecto')],
                'id_estado'   => ['required', Rule::exists('estados', 'id_estado')],
                'torres' => ['required', 'array', 'min:1'],
                'torres.*.nombre_torre'       => ['required', 'string', 'max:50'],
                'torres.*.numero_pisos'       => ['nullable', 'integer', 'min:1', 'max:32767'],
                'torres.*.nivel_inicio_prima' => ['required', 'integer', 'min:1'],
            ]);

            DB::transaction(function () use ($validated) {
                foreach ($validated['torres'] as $t) {
                    Torre::create([
                        'nombre_torre'       => $t['nombre_torre'],
                        'numero_pisos'       => $t['numero_pisos'] ?? null,
                        'nivel_inicio_prima' => $t['nivel_inicio_prima'],
                        'id_proyecto'        => $validated['id_proyecto'],
                        'id_estado'          => $validated['id_estado'],
                    ]);
                }
            });

            $count = count($validated['torres']);

            return redirect()
                ->route('pisostorre.create', [
                    'proyecto' => $validated['id_proyecto'],
                    // opcional si quieres preselección también del estado:
                    // 'estado' => $validated['id_estado'],
                ])
                ->with('success', $count === 1 ? 'Torre creada exitosamente.' : "{$count} torres creadas exitosamente.")
                ->with('count', $count);
        }

        $validated = $request->validate([
            'nombre_torre' => ['required', 'string', 'max:50'],
            'numero_pisos' => ['nullable', 'integer', 'min:1', 'max:32767'],
            'nivel_inicio_prima' => ['required', 'integer', 'min:1'],
            'id_proyecto' => ['required', Rule::exists('proyectos', 'id_proyecto')],
            'id_estado' => ['required', Rule::exists('estados', 'id_estado')],
        ]);

        $torre = Torre::create($validated);

        return redirect()
            ->route('pisostorre.create', [
                'proyecto' => $validated['id_proyecto'],
                // opcional:
                // 'estado' => $validated['id_estado'],
            ])
            ->with('success', 'Torre creada exitosamente')
            ->with('id_torre', $torre->id_torre);
    }

    public function show(Request $request, $id_torre)
    {
        $empleado = $request->user()->load('cargo');

        $torre = Torre::with([
            'proyecto.ubicacion.ciudad',
            'estado',
            'pisos',
            'apartamentos.tipoApartamento',
            'apartamentos.estadoInmueble'
        ])->findOrFail($id_torre);

        return Inertia::render('Admin/Torres/Show', [
            'torre' => $torre,
            'empleado' => $empleado,
        ]);
    }

    public function edit(Request $request, $id_torre)
    {
        $empleado = $request->user()->load('cargo');
        $torre = Torre::with(['proyecto', 'estado'])->findOrFail($id_torre);

        $proyectos = Proyecto::select('id_proyecto', 'nombre')->orderBy('nombre')->get();
        $estados = Estado::select('id_estado', 'nombre')->orderBy('nombre')->get();

        return Inertia::render('Admin/Torres/Edit', [
            'torre' => $torre,
            'proyectos' => $proyectos,
            'estados' => $estados,
            'empleado' => $empleado,
        ]);
    }

    public function update(Request $request, $id_torre)
    {
        $torre = Torre::findOrFail($id_torre);

        $validated = $request->validate([
            'nombre_torre' => ['required', 'string', 'max:50'],
            'numero_pisos' => ['nullable', 'integer', 'min:1', 'max:32767'],
            'nivel_inicio_prima' => ['required', 'integer', 'min:1'],
            'id_proyecto' => ['required', Rule::exists('proyectos', 'id_proyecto')],
            'id_estado' => ['required', Rule::exists('estados', 'id_estado')],
        ]);

        $torre->update($validated);

        return redirect()
            ->route('admin.torres.show', $torre->id_torre)
            ->with('success', 'Torre actualizada exitosamente');
    }

    public function destroy($id_torre)
    {
        $torre = Torre::withCount(['pisos', 'apartamentos'])->findOrFail($id_torre);

        if ($torre->pisos_count > 0 || $torre->apartamentos_count > 0) {
            return back()->withErrors([
                'general' => 'No se puede eliminar la torre porque tiene ' .
                    ($torre->pisos_count > 0 ? $torre->pisos_count . ' piso(s)' : '') .
                    ($torre->pisos_count > 0 && $torre->apartamentos_count > 0 ? ' y ' : '') .
                    ($torre->apartamentos_count > 0 ? $torre->apartamentos_count . ' apartamento(s)' : '') . ' asociado(s)'
            ]);
        }

        $torre->delete();

        return redirect()
            ->route('admin.torres.index')
            ->with('success', 'Torre eliminada exitosamente');
    }
}
