<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\Proyecto;
use App\Models\Apartamento;
use App\Models\Local;
use App\Models\EstadoInmueble;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CatalogoWebController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo');

        // Filtros recibidos desde el frontend
        $proyectoId = $request->get('proyecto');
        $tipoInmueble = $request->get('tipo'); // apartamento | local
        $precioMin = $request->get('precio_min');
        $precioMax = $request->get('precio_max');
        $search = $request->get('search');

        // Estado: Disponible
        $estadoDisponible = EstadoInmueble::where('nombre', 'Disponible')->first();

        if (!$estadoDisponible) {
            return Inertia::render('Ventas/Catalogo/Index', [
                'proyectos' => [],
                'inmuebles' => [],
                'empleado' => $empleado,
                'filters' => $request->only(['proyecto', 'tipo', 'precio_min', 'precio_max', 'search']),
            ]);
        }

        // ========================
        // PROYECTOS DISPONIBLES
        // ========================
        $proyectos = Proyecto::with(['ubicacion.ciudad'])
            ->whereHas('torres.apartamentos', function ($q) use ($estadoDisponible) {
                $q->where('id_estado_inmueble', $estadoDisponible->id_estado_inmueble);
            })
            ->orWhereHas('torres.locales', function ($q) use ($estadoDisponible) {
                $q->where('id_estado_inmueble', $estadoDisponible->id_estado_inmueble);
            })
            ->select('id_proyecto', 'nombre', 'id_ubicacion')
            ->orderBy('nombre')
            ->get();

        // ========================
        // APARTAMENTOS
        // ========================
        $apartamentos = Apartamento::with([
            'tipoApartamento',
            'torre.proyecto.ubicacion.ciudad',
            'pisoTorre',
            'estadoInmueble',
        ])
            ->where('id_estado_inmueble', $estadoDisponible->id_estado_inmueble)

            // Filtro por proyecto
            ->when(
                $proyectoId,
                fn($q) =>
                $q->whereHas(
                    'torre',
                    fn($p) =>
                    $p->where('id_proyecto', $proyectoId)
                )
            )

            // Filtro por precio
            ->when($precioMin, fn($q) => $q->where('valor_final', '>=', $precioMin))
            ->when($precioMax, fn($q) => $q->where('valor_final', '<=', $precioMax))

            // Tipo de inmueble
            ->when(
                $tipoInmueble === 'local',
                fn($q) =>
                $q->whereRaw('1 = 0')
            )

            // Búsqueda
            ->when($search, function ($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                    ->orWhereHas(
                        'torre.proyecto',
                        fn($p) =>
                        $p->where('nombre', 'like', "%{$search}%")
                    )
                    ->orWhereHas(
                        'tipoApartamento',
                        fn($t) =>
                        $t->where('nombre', 'like', "%{$search}%")
                    );
            })

            // ORDEN: número incremental (Postgres)
            ->orderByRaw("
      CASE
        WHEN numero ~ '^[0-9]+$' THEN LPAD(numero, 10, '0')
        ELSE numero
      END ASC
    ")

            ->get()
            ->map(function ($apto) {
                return [
                    'id' => $apto->id_apartamento,
                    'tipo' => 'apartamento',
                    'numero' => $apto->numero,
                    'proyecto' => $apto->torre->proyecto->nombre,
                    'id_proyecto' => $apto->torre->proyecto->id_proyecto,
                    'torre' => $apto->torre->nombre_torre,
                    'piso' => $apto->pisoTorre->nivel,
                    'tipo_inmueble' => $apto->tipoApartamento->nombre,
                    'area_construida' => $apto->tipoApartamento->area_construida,
                    'area_privada' => $apto->tipoApartamento->area_privada,
                    'habitaciones' => $apto->tipoApartamento->cantidad_habitaciones,
                    'banos' => $apto->tipoApartamento->cantidad_banos,
                    'valor' => $apto->valor_final ?? 0,
                    'valor_final' => $apto->valor_final,
                    'valor_comercial' => $apto->valor_comercial,
                    'valor_m2' => $apto->tipoApartamento->valor_m2,
                    'ubicacion' => $apto->torre->proyecto->ubicacion->ciudad->nombre,
                    'direccion' => $apto->torre->proyecto->ubicacion->direccion,
                    'estado' => $apto->estadoInmueble->nombre,
                ];
            });

        // ========================
        // LOCALES
        // ========================
        $locales = Local::with([
            'torre.proyecto.ubicacion.ciudad',
            'pisoTorre',
            'estadoInmueble'
        ])
            ->where('id_estado_inmueble', $estadoDisponible->id_estado_inmueble)

            ->when(
                $proyectoId,
                fn($q) =>
                $q->whereHas(
                    'torre',
                    fn($p) =>
                    $p->where('id_proyecto', $proyectoId)
                )
            )

            ->when($precioMin, fn($q) => $q->where('valor_total', '>=', $precioMin))
            ->when($precioMax, fn($q) => $q->where('valor_total', '<=', $precioMax))

            ->when(
                $tipoInmueble === 'apartamento',
                fn($q) =>
                $q->whereRaw('1 = 0')
            )

            ->when($search, function ($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                    ->orWhereHas(
                        'torre.proyecto',
                        fn($p) =>
                        $p->where('nombre', 'like', "%{$search}%")
                    );
            })

            // ORDEN: número incremental (Postgres)
            ->orderByRaw("
      CASE
        WHEN numero ~ '^[0-9]+$' THEN LPAD(numero, 10, '0')
        ELSE numero
      END ASC
    ")

            ->get()
            ->map(function ($local) {
                return [
                    'id' => $local->id_local,
                    'tipo' => 'local',
                    'numero' => $local->numero,
                    'proyecto' => $local->torre->proyecto->nombre,
                    'id_proyecto' => $local->torre->proyecto->id_proyecto,
                    'torre' => $local->torre->nombre_torre,
                    'piso' => $local->pisoTorre->nivel,
                    'tipo_inmueble' => 'Local Comercial',
                    'area_construida' => $local->area_total_local,
                    'area_privada' => $local->area_total_local,
                    'habitaciones' => null,
                    'banos' => null,
                    'valor' => $local->valor_total ?? 0,
                    'valor_final' => $local->valor_total,
                    'valor_comercial' => $local->valor_comercial,
                    'ubicacion' => $local->torre->proyecto->ubicacion->ciudad->nombre,
                    'direccion' => $local->torre->proyecto->ubicacion->direccion,
                    'estado' => $local->estadoInmueble->nombre,
                ];
            });

        // ========================
        // COMBINAR Y ORDENAR
        // ========================
        $inmuebles = $apartamentos
            ->concat($locales)
            ->sort(function ($a, $b) {
                // orden natural por numero (ej: 2 < 10 < 100)
                return strnatcasecmp((string) $a['numero'], (string) $b['numero']);
            })
            ->values();

        return Inertia::render('Ventas/Catalogo/Index', [
            'proyectos' => $proyectos,
            'inmuebles' => $inmuebles,
            'empleado' => $empleado,
            'filters' => $request->only(['proyecto', 'tipo', 'precio_min', 'precio_max', 'search']),
        ]);
    }

    // ======================================
    // SHOW - DETALLE DE INMUEBLE
    // ======================================
    public function show(Request $request, $tipo, $id)
    {
        $empleado = $request->user()->load('cargo');

        if ($tipo === 'apartamento') {
            $inmueble = Apartamento::with([
                'tipoApartamento',
                'torre.proyecto.ubicacion.ciudad',
                'pisoTorre',
                'estadoInmueble',
                'parqueaderos'
            ])->findOrFail($id);

            $data = [
                'id' => $inmueble->id_apartamento,
                'tipo' => 'apartamento',
                'numero' => $inmueble->numero,
                'proyecto' => $inmueble->torre->proyecto->nombre,
                'id_proyecto' => $inmueble->torre->proyecto->id_proyecto,
                'torre' => $inmueble->torre->nombre_torre,
                'piso' => $inmueble->pisoTorre->nivel,
                'tipo_inmueble' => $inmueble->tipoApartamento->nombre,
                'area_construida' => $inmueble->tipoApartamento->area_construida,
                'area_privada' => $inmueble->tipoApartamento->area_privada,
                'habitaciones' => $inmueble->tipoApartamento->cantidad_habitaciones,
                'banos' => $inmueble->tipoApartamento->cantidad_banos,
                'valor_m2' => $inmueble->tipoApartamento->valor_m2,
                'valor_base' => $inmueble->valor_total,
                'prima_altura' => $inmueble->prima_altura,
                'valor_politica' => $inmueble->valor_politica,
                'valor_final' => $inmueble->valor_final,
                'valor_comercial' => $inmueble->valor_comercial,
                'cuota_inicial' => $inmueble->torre->proyecto->porcentaje_cuota_inicial_min
                    ? $inmueble->valor_final * ($inmueble->torre->proyecto->porcentaje_cuota_inicial_min / 100)
                    : 0,
                'ubicacion' => $inmueble->torre->proyecto->ubicacion->ciudad->nombre,
                'direccion' => $inmueble->torre->proyecto->ubicacion->direccion,
                'estado' => $inmueble->estadoInmueble->nombre,
                'parqueaderos' => $inmueble->parqueaderos->count(),
            ];
        } else {

            $inmueble = Local::with([
                'torre.proyecto.ubicacion.ciudad',
                'pisoTorre',
                'estadoInmueble'
            ])->findOrFail($id);

            $data = [
                'id' => $inmueble->id_local,
                'tipo' => 'local',
                'numero' => $inmueble->numero,
                'proyecto' => $inmueble->torre->proyecto->nombre,
                'id_proyecto' => $inmueble->torre->proyecto->id_proyecto,
                'torre' => $inmueble->torre->nombre_torre,
                'piso' => $inmueble->pisoTorre->nivel,
                'tipo_inmueble' => 'Local Comercial',
                'area_construida' => $inmueble->area_total_local,
                'area_privada' => $inmueble->area_total_local,
                'habitaciones' => null,
                'banos' => null,
                'valor_m2' => $inmueble->valor_m2,
                'valor_base' => $inmueble->valor_total,
                'prima_altura' => 0,
                'valor_politica' => 0,
                'valor_final' => $inmueble->valor_total,
                'valor_comercial' => $inmueble->valor_comercial,
                'cuota_inicial' => $inmueble->torre->proyecto->porcentaje_cuota_inicial_min
                    ? $inmueble->valor_total * ($inmueble->torre->proyecto->porcentaje_cuota_inicial_min / 100)
                    : 0,

                'ubicacion' => $inmueble->torre->proyecto->ubicacion->ciudad->nombre,
                'direccion' => $inmueble->torre->proyecto->ubicacion->direccion,
                'estado' => $inmueble->estadoInmueble->nombre,
                'parqueaderos' => 0,
            ];
        }

        return Inertia::render('Ventas/Catalogo/Show', [
            'inmueble' => $data,
            'empleado' => $empleado,
        ]);
    }
}
