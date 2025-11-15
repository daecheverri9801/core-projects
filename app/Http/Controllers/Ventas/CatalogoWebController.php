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

        // Obtener el estado "Disponible"
        $estadoDisponible = EstadoInmueble::where('nombre', 'Disponible')->first();

        if (!$estadoDisponible) {
            return Inertia::render('Ventas/Catalogo/Index', [
                'proyectos' => [],
                'empleado' => $empleado,
                'filters' => $request->only(['proyecto', 'tipo', 'precio_min', 'precio_max']),
            ]);
        }

        // Filtros
        $proyectoId = $request->get('proyecto');
        $tipoInmueble = $request->get('tipo'); // 'apartamento' o 'local'
        $precioMin = $request->get('precio_min');
        $precioMax = $request->get('precio_max');

        // Obtener proyectos con inmuebles disponibles
        $proyectos = Proyecto::with([
            'ubicacion.ciudad',
            'estado_proyecto'
        ])
            ->whereHas('torres.apartamentos', function ($q) use ($estadoDisponible) {
                $q->where('id_estado_inmueble', $estadoDisponible->id_estado_inmueble);
            })
            ->orWhereHas('torres.locales', function ($q) use ($estadoDisponible) {
                $q->where('id_estado_inmueble', $estadoDisponible->id_estado_inmueble);
            })
            ->select('id_proyecto', 'nombre', 'descripcion', 'id_ubicacion', 'id_estado')
            ->orderBy('nombre')
            ->get();

        // Obtener apartamentos disponibles
        $apartamentos = Apartamento::with([
            'tipoApartamento',
            'torre.proyecto.ubicacion.ciudad',
            'pisoTorre',
            'estadoInmueble'
        ])
            ->where('id_estado_inmueble', $estadoDisponible->id_estado_inmueble)
            ->when($proyectoId, function ($q) use ($proyectoId) {
                $q->whereHas('torre', fn($query) => $query->where('id_proyecto', $proyectoId));
            })
            ->when($precioMin, fn($q) => $q->where('valor_final', '>=', $precioMin))
            ->when($precioMax, fn($q) => $q->where('valor_final', '<=', $precioMax))
            ->when(!$tipoInmueble || $tipoInmueble === 'apartamento', fn($q) => $q)
            ->when($tipoInmueble === 'local', fn($q) => $q->whereRaw('1 = 0')) // Excluir si filtro es "local"
            ->orderBy('valor_final')
            ->get()
            ->map(function ($apto) {
                return [
                    'id' => $apto->id_apartamento,
                    'tipo' => 'apartamento',
                    'numero' => $apto->numero,
                    'proyecto' => $apto->torre->proyecto->nombre ?? '—',
                    'torre' => $apto->torre->nombre_torre ?? '—',
                    'piso' => $apto->pisoTorre->nivel ?? '—',
                    'tipo_inmueble' => $apto->tipoApartamento->nombre ?? '—',
                    'area_construida' => $apto->tipoApartamento->area_construida ?? 0,
                    'area_privada' => $apto->tipoApartamento->area_privada ?? 0,
                    'habitaciones' => $apto->tipoApartamento->cantidad_habitaciones ?? 0,
                    'banos' => $apto->tipoApartamento->cantidad_banos ?? 0,
                    'valor' => $apto->valor_final ?? $apto->valor_total ?? 0,
                    'ubicacion' => $apto->torre->proyecto->ubicacion->ciudad->nombre ?? '—',
                    'estado' => $apto->estadoInmueble->nombre ?? '—',
                ];
            });

        // Obtener locales disponibles
        $locales = Local::with([
            'torre.proyecto.ubicacion.ciudad',
            'pisoTorre',
            'estadoInmueble'
        ])
            ->where('id_estado_inmueble', $estadoDisponible->id_estado_inmueble)
            ->when($proyectoId, function ($q) use ($proyectoId) {
                $q->whereHas('torre', fn($query) => $query->where('id_proyecto', $proyectoId));
            })
            ->when($precioMin, fn($q) => $q->where('valor_total', '>=', $precioMin))
            ->when($precioMax, fn($q) => $q->where('valor_total', '<=', $precioMax))
            ->when(!$tipoInmueble || $tipoInmueble === 'local', fn($q) => $q)
            ->when($tipoInmueble === 'apartamento', fn($q) => $q->whereRaw('1 = 0')) // Excluir si filtro es "apartamento"
            ->orderBy('valor_total')
            ->get()
            ->map(function ($local) {
                return [
                    'id' => $local->id_local,
                    'tipo' => 'local',
                    'numero' => $local->numero,
                    'proyecto' => $local->torre->proyecto->nombre ?? '—',
                    'torre' => $local->torre->nombre_torre ?? '—',
                    'piso' => $local->pisoTorre->nivel ?? '—',
                    'tipo_inmueble' => 'Local Comercial',
                    'area_construida' => $local->area_total_local ?? 0,
                    'area_privada' => $local->area_total_local ?? 0,
                    'habitaciones' => null,
                    'banos' => null,
                    'valor' => $local->valor_total ?? 0,
                    'ubicacion' => $local->torre->proyecto->ubicacion->ciudad->nombre ?? '—',
                    'estado' => $local->estadoInmueble->nombre ?? '—',
                ];
            });

        // Combinar y ordenar
        $inmuebles = $apartamentos->concat($locales)->sortBy('valor')->values();

        return Inertia::render('Ventas/Catalogo/Index', [
            'proyectos' => $proyectos,
            'inmuebles' => $inmuebles,
            'empleado' => $empleado,
            'filters' => $request->only(['proyecto', 'tipo', 'precio_min', 'precio_max']),
        ]);
    }

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
                'proyecto' => $inmueble->torre->proyecto->nombre ?? '—',
                'id_proyecto' => $inmueble->torre->proyecto->id_proyecto ?? null,
                'torre' => $inmueble->torre->nombre_torre ?? '—',
                'piso' => $inmueble->pisoTorre->nivel ?? '—',
                'tipo_inmueble' => $inmueble->tipoApartamento->nombre ?? '—',
                'area_construida' => $inmueble->tipoApartamento->area_construida ?? 0,
                'area_privada' => $inmueble->tipoApartamento->area_privada ?? 0,
                'habitaciones' => $inmueble->tipoApartamento->cantidad_habitaciones ?? 0,
                'banos' => $inmueble->tipoApartamento->cantidad_banos ?? 0,
                'valor_m2' => $inmueble->tipoApartamento->valor_m2 ?? 0,
                'valor_base' => $inmueble->valor_total ?? 0,
                'prima_altura' => $inmueble->prima_altura ?? 0,
                'valor_politica' => $inmueble->valor_politica ?? 0,
                'valor_final' => $inmueble->valor_final ?? $inmueble->valor_total ?? 0,
                'ubicacion' => $inmueble->torre->proyecto->ubicacion->ciudad->nombre ?? '—',
                'direccion' => $inmueble->torre->proyecto->ubicacion->direccion ?? '—',
                'estado' => $inmueble->estadoInmueble->nombre ?? '—',
                'parqueaderos' => $inmueble->parqueaderos->count() ?? 0,
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
                'proyecto' => $inmueble->torre->proyecto->nombre ?? '—',
                'id_proyecto' => $inmueble->torre->proyecto->id_proyecto ?? null,
                'torre' => $inmueble->torre->nombre_torre ?? '—',
                'piso' => $inmueble->pisoTorre->nivel ?? '—',
                'tipo_inmueble' => 'Local Comercial',
                'area_construida' => $inmueble->area_total_local ?? 0,
                'area_privada' => $inmueble->area_total_local ?? 0,
                'habitaciones' => null,
                'banos' => null,
                'valor_m2' => $inmueble->valor_m2 ?? 0,
                'valor_base' => $inmueble->valor_total ?? 0,
                'prima_altura' => 0,
                'valor_politica' => 0,
                'valor_final' => $inmueble->valor_total ?? 0,
                'ubicacion' => $inmueble->torre->proyecto->ubicacion->ciudad->nombre ?? '—',
                'direccion' => $inmueble->torre->proyecto->ubicacion->direccion ?? '—',
                'estado' => $inmueble->estadoInmueble->nombre ?? '—',
                'parqueaderos' => 0,
            ];
        }

        return Inertia::render('Ventas/Catalogo/Show', [
            'inmueble' => $data,
            'empleado' => $empleado,
        ]);
    }
}
