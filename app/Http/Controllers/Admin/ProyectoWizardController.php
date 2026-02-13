<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proyecto;
use App\Models\Estado;
use App\Models\Ubicacion;

// Ajusta los modelos según tu proyecto real:
use App\Models\PoliticaPrecioProyecto;
use App\Models\Torre;
use App\Models\PisoTorre;
use App\Models\TipoApartamento;
use App\Models\EstadoInmueble;
use App\Models\Apartamento;
use App\Models\Local;
use App\Models\Parqueadero;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProyectoWizardController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo');

        $proyectoId = $request->query('proyecto_id');
        $tab = $request->query('tab', '1'); // 1..7

        $estados = Estado::orderBy('nombre')->get();
        $ubicaciones = Ubicacion::with('ciudad')->orderBy('direccion')->get();

        $proyecto = null;
        $torres = [];
        $pisos = [];
        $tiposApartamento = [];
        $estadosInmueble = [];
        $apartamentosLite = []; // para tab7 asignación (opcional)

        if ($proyectoId) {
            $proyecto = Proyecto::with([
                'estado_proyecto',
                'ubicacion.ciudad.departamento.pais',
            ])->find($proyectoId);

            if ($proyecto) {
                // Cargas "ligeras" para alimentar selects del wizard
                $torres = Torre::where('id_proyecto', $proyecto->id_proyecto)
                    ->select('id_torre', 'nombre_torre', 'nivel_inicio_prima')
                    ->orderBy('id_torre', 'asc')
                    ->get();

                $pisos = PisoTorre::whereIn('id_torre', collect($torres)->pluck('id_torre'))
                    ->select('id_piso_torre', 'id_torre', 'nivel', 'uso')
                    ->orderBy('nivel', 'asc')
                    ->get();

                $tiposApartamento = TipoApartamento::where('id_proyecto', $proyecto->id_proyecto)
                    ->select('id_tipo_apartamento', 'id_proyecto', 'nombre', 'valor_estimado')
                    ->orderBy('id_tipo_apartamento', 'asc')
                    ->get();

                $estadosInmueble = EstadoInmueble::query()
                    ->select('id_estado_inmueble', 'nombre')
                    ->orderBy('nombre', 'asc')
                    ->get();

                // Para tab7 asignación opcional a apartamento
                $apartamentosLite = Apartamento::query()
                    ->whereHas('pisoTorre.torre', fn($q) => $q->where('id_proyecto', $proyecto->id_proyecto))
                    ->with(['pisoTorre.torre:id_torre,id_proyecto,nombre_torre', 'pisoTorre:id_piso_torre,id_torre,nivel'])
                    ->select('id_apartamento', 'numero', 'id_piso_torre')
                    ->orderBy('id_apartamento', 'desc')
                    ->limit(200)
                    ->get()
                    ->map(function ($a) use ($proyecto) {
                        return [
                            'id_apartamento' => $a->id_apartamento,
                            'numero' => $a->numero,
                            'torre' => $a->pisoTorre?->torre?->nombre_torre,
                            'proyecto' => $proyecto->nombre,
                        ];
                    });
            }
        }

        return Inertia::render('Admin/Proyectos/Wizard/CreateWizard', [
            'empleado' => $empleado,
            'tab' => (string)$tab,
            'proyectoId' => $proyecto?->id_proyecto,
            'proyecto' => $proyecto,

            'estadosProyecto' => $estados,
            'estados' => $estados,
            'ubicaciones' => $ubicaciones,

            // data auxiliar para tabs:
            'torres' => $torres,
            'pisos' => $pisos,
            'tiposApartamento' => $tiposApartamento,
            'estadosInmueble' => $estadosInmueble,
            'apartamentosLite' => $apartamentosLite,
        ]);
    }

    // ------------------------
    // TAB 1 - PROYECTO
    // ------------------------
    public function storeProyecto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:500',
            'fecha_inicio' => 'nullable|date',
            'fecha_finalizacion' => 'nullable|date|after_or_equal:fecha_inicio',

            'presupuesto_inicial' => 'nullable|numeric|min:0',
            'presupuesto_final' => 'nullable|numeric|min:0',
            'metros_construidos' => 'nullable|numeric|min:0',

            'cantidad_locales' => 'nullable|integer|min:0',
            'cantidad_apartamentos' => 'nullable|integer|min:0',
            'cantidad_parqueaderos_vehiculo' => 'nullable|integer|min:0',
            'cantidad_parqueaderos_moto' => 'nullable|integer|min:0',

            'estrato' => 'nullable|integer|min:1|max:6',
            'numero_pisos' => 'nullable|integer|min:1|max:32767',
            'numero_torres' => 'nullable|integer|min:1|max:32767',

            'porcentaje_cuota_inicial_min' => 'nullable|numeric|min:0|max:100',
            'valor_min_separacion' => 'nullable|numeric|min:0',
            'plazo_cuota_inicial_meses' => 'nullable|integer|min:1|max:32767',
            'plazo_max_separacion_dias' => 'nullable|integer|min:1|max:3650',

            'id_estado' => 'required|exists:estados,id_estado',
            'id_ubicacion' => 'required|exists:ubicaciones,id_ubicacion',

            'prima_altura_base' => 'nullable|numeric|min:0',
            'prima_altura_incremento' => 'nullable|numeric|min:0',
            'prima_altura_activa' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $proyecto = Proyecto::create($validator->validated());

        // Mantener usuario en wizard (misma vista) y ya con proyecto_id
        return redirect()->route('proyectos.wizard', [
            'proyecto_id' => $proyecto->id_proyecto,
            'tab' => 2, // sugerencia: continuar en tab2
        ])->with('success', 'Proyecto creado. Continúa con las demás pestañas.');
    }

    public function updateProyecto(Request $request, Proyecto $proyecto)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:500',
            'fecha_inicio' => 'nullable|date',
            'fecha_finalizacion' => 'nullable|date|after_or_equal:fecha_inicio',

            'presupuesto_inicial' => 'nullable|numeric|min:0',
            'presupuesto_final' => 'nullable|numeric|min:0',
            'metros_construidos' => 'nullable|numeric|min:0',

            'cantidad_locales' => 'nullable|integer|min:0',
            'cantidad_apartamentos' => 'nullable|integer|min:0',
            'cantidad_parqueaderos_vehiculo' => 'nullable|integer|min:0',
            'cantidad_parqueaderos_moto' => 'nullable|integer|min:0',

            'estrato' => 'nullable|integer|min:1|max:6',
            'numero_pisos' => 'nullable|integer|min:1|max:32767',
            'numero_torres' => 'nullable|integer|min:1|max:32767',

            'porcentaje_cuota_inicial_min' => 'nullable|numeric|min:0|max:100',
            'valor_min_separacion' => 'nullable|numeric|min:0',
            'plazo_cuota_inicial_meses' => 'nullable|integer|min:1|max:32767',
            'plazo_max_separacion_dias' => 'nullable|integer|min:1|max:3650',

            'id_estado' => 'required|exists:estados,id_estado',
            'id_ubicacion' => 'required|exists:ubicaciones,id_ubicacion',

            'prima_altura_base' => 'nullable|numeric|min:0',
            'prima_altura_incremento' => 'nullable|numeric|min:0',
            'prima_altura_activa' => 'nullable|boolean',
        ]);

        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        $proyecto->update($validator->validated());

        return back()->with('success', 'Información del proyecto actualizada.');
    }

    // ------------------------
    // TAB 2 - POLITICAS PRECIO
    // ------------------------
    public function storePoliticaPrecio(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_proyecto' => 'required|exists:proyectos,id_proyecto',
            'ventas_por_escalon' => 'nullable|integer|min:1',
            'porcentaje_aumento' => 'nullable|numeric|min:0|max:999.999',
            'aplica_desde' => 'nullable|date',
            'estado' => 'required|boolean',
        ]);

        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        PoliticaPrecioProyecto::create($validator->validated());

        return back()->with('success', 'Política de precio creada.');
    }

    // ------------------------
    // TAB 3 - TORRES (LOTE)
    // ------------------------
    public function storeTorres(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_proyecto' => 'required|exists:proyectos,id_proyecto',
            'id_estado' => 'required|exists:estados,id_estado',
            'torres' => 'required|array|min:1',
            'torres.*.nombre_torre' => 'required|string|max:150',
            'torres.*.numero_pisos' => 'nullable|integer|min:1|max:32767',
            'torres.*.nivel_inicio_prima' => 'required|integer|min:1|max:32767',
        ]);

        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        $data = $validator->validated();

        DB::transaction(function () use ($data) {
            foreach ($data['torres'] as $t) {
                Torre::create([
                    'id_proyecto' => $data['id_proyecto'],
                    'id_estado' => $data['id_estado'],
                    'nombre_torre' => $t['nombre_torre'],
                    'numero_pisos' => $t['numero_pisos'] ?? null,
                    'nivel_inicio_prima' => $t['nivel_inicio_prima'],
                ]);
            }
        });

        return back()->with('success', 'Torres creadas correctamente.');
    }

    // ------------------------
    // TAB 4 - PISOS TORRE (LOTE)
    // ------------------------
    public function storePisosTorre(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_torre' => 'required|exists:torres,id_torre',
            'pisos' => 'required|array|min:1',
            'pisos.*.nivel' => 'required|integer|min:1|max:32767',
            'pisos.*.uso' => 'nullable|string|max:40',
        ]);

        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        $data = $validator->validated();

        // Evitar niveles duplicados en el mismo payload
        $niveles = collect($data['pisos'])->pluck('nivel');
        if ($niveles->count() !== $niveles->unique()->count()) {
            return back()->withErrors(['pisos' => 'Hay niveles duplicados en la carga.'])->withInput();
        }

        DB::transaction(function () use ($data) {
            foreach ($data['pisos'] as $p) {
                PisoTorre::create([
                    'id_torre' => $data['id_torre'],
                    'nivel' => $p['nivel'],
                    'uso' => $p['uso'] ?? null,
                ]);
            }
        });

        return back()->with('success', 'Pisos creados correctamente.');
    }

    // ------------------------
    // TAB 5 - TIPOS APARTAMENTO (LOTE + IMAGEN)
    // ------------------------
    public function storeTiposApartamento(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_proyecto' => 'required|exists:proyectos,id_proyecto',
            'tipos' => 'required|array|min:1',
            'tipos.*.nombre' => 'required|string|max:100',
            'tipos.*.area_construida' => 'nullable|numeric|min:0',
            'tipos.*.area_privada' => 'nullable|numeric|min:0',
            'tipos.*.cantidad_habitaciones' => 'nullable|integer|min:0',
            'tipos.*.cantidad_banos' => 'nullable|integer|min:0',
            'tipos.*.valor_m2' => 'nullable|numeric|min:0',
            'tipos.*.imagen' => 'nullable|image|max:5120', // 5MB
        ]);

        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        $idProyecto = $request->input('id_proyecto');
        $tipos = $request->input('tipos', []);

        DB::transaction(function () use ($idProyecto, $tipos, $request) {
            foreach ($tipos as $idx => $t) {
                $imagenPath = null;
                // Archivos vienen en request como tipos.0.imagen
                if ($request->hasFile("tipos.$idx.imagen")) {
                    $file = $request->file("tipos.$idx.imagen");
                    $imagenPath = $file->store('tipos_apartamento', 'public');
                }

                $area = (float)($t['area_construida'] ?? 0);
                $v2 = (float)($t['valor_m2'] ?? 0);
                $valorEstimado = $area > 0 && $v2 > 0 ? ($area * $v2) : 0;

                TipoApartamento::create([
                    'id_proyecto' => $idProyecto,
                    'nombre' => $t['nombre'],
                    'area_construida' => $t['area_construida'] ?? null,
                    'area_privada' => $t['area_privada'] ?? null,
                    'cantidad_habitaciones' => $t['cantidad_habitaciones'] ?? null,
                    'cantidad_banos' => $t['cantidad_banos'] ?? null,
                    'valor_m2' => $t['valor_m2'] ?? null,
                    'valor_estimado' => $valorEstimado,
                    'imagen' => $imagenPath,
                ]);
            }
        });

        return back()->with('success', 'Tipos de apartamento creados correctamente.');
    }

    // ------------------------
    // TAB 6A - APARTAMENTOS (LOTE)
    // ------------------------
    public function storeApartamentos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_torre' => 'required|exists:torres,id_torre',
            'apartamentos' => 'required|array|min:1',
            'apartamentos.*.numero' => 'required|string|max:20',
            'apartamentos.*.id_piso_torre' => 'required|exists:piso_torre,id_piso_torre',
            'apartamentos.*.id_tipo_apartamento' => 'required|exists:tipos_apartamento,id_tipo_apartamento',
            'apartamentos.*.id_estado_inmueble' => 'required|exists:estados_inmueble,id_estado_inmueble',
        ]);

        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        $data = $validator->validated();

        DB::transaction(function () use ($data) {
            foreach ($data['apartamentos'] as $a) {
                Apartamento::create([
                    'numero' => $a['numero'],
                    'id_piso_torre' => $a['id_piso_torre'],
                    'id_tipo_apartamento' => $a['id_tipo_apartamento'],
                    'id_estado_inmueble' => $a['id_estado_inmueble'],
                ]);
            }
        });

        return back()->with('success', 'Apartamentos creados correctamente.');
    }

    // ------------------------
    // TAB 6B - LOCALES
    // (Implementación simple: uno o varios en array "locales")
    // ------------------------
    public function storeLocales(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'locales' => 'required|array|min:1',
            'locales.*.numero' => 'required|string|max:20',
            'locales.*.id_torre' => 'required|exists:torres,id_torre',
            'locales.*.id_piso_torre' => 'required|exists:piso_torre,id_piso_torre',
            'locales.*.id_estado_inmueble' => 'required|exists:estados_inmueble,id_estado_inmueble',
            'locales.*.area_total_local' => 'nullable|numeric|min:0',
            'locales.*.valor_m2' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        $data = $validator->validated();

        DB::transaction(function () use ($data) {
            foreach ($data['locales'] as $l) {
                Local::create([
                    'numero' => $l['numero'],
                    'id_torre' => $l['id_torre'],
                    'id_piso_torre' => $l['id_piso_torre'],
                    'id_estado_inmueble' => $l['id_estado_inmueble'],
                    'area_total_local' => $l['area_total_local'] ?? null,
                    'valor_m2' => $l['valor_m2'] ?? null,
                ]);
            }
        });

        return back()->with('success', 'Locales creados correctamente.');
    }

    // ------------------------
    // TAB 7 - PARQUEADEROS (redirige al index proyectos)
    // Implementación: uno o varios en array "parqueaderos"
    // ------------------------
    public function storeParqueaderos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'parqueaderos' => 'required|array|min:1',
            'parqueaderos.*.numero' => 'required|string|max:20',
            'parqueaderos.*.tipo' => ['required', Rule::in(['Vehiculo', 'Moto'])],
            'parqueaderos.*.id_apartamento' => 'nullable|exists:apartamentos,id_apartamento',
        ]);

        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        $data = $validator->validated();

        DB::transaction(function () use ($data) {
            foreach ($data['parqueaderos'] as $p) {
                Parqueadero::create([
                    'numero' => $p['numero'],
                    'tipo' => $p['tipo'],
                    'id_apartamento' => $p['id_apartamento'] ?? null,
                ]);
            }
        });

        return redirect()->route('proyectos.index')->with('success', 'Parqueaderos creados. Proyecto finalizado.');
    }
}
