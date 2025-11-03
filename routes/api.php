<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaisController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\CiudadController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\EstadoInmuebleController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\DependenciaController;
use App\Http\Controllers\TipoApartamentoController;
use App\Http\Controllers\UbicacionController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\TorreController;
use App\Http\Controllers\PisoTorreController;
use App\Http\Controllers\ApartamentoController;
use App\Http\Controllers\LocalController;
use App\Http\Controllers\ParqueaderoController;
use App\Http\Controllers\ZonaSocialController;
use App\Http\Controllers\PoliticaComisionController;
use App\Http\Controllers\PoliticaPrecioProyectoController;
use App\Http\Controllers\AuthController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::post('/login', [AuthController::class, 'login']);

// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('/logout', [AuthController::class, 'logout']);
//     Route::get('/user', [AuthController::class, 'user']);
// });

// Route::middleware(['auth:sanctum', 'cargo:1'])->group(function () {
//     Route::get('paises/', [PaisController::class, 'index']);
// });

// ============================================
// RUTAS DE UBICACIÓN GEOGRÁFICA
// ============================================

// Países
Route::prefix('paises')->group(function () {;
    Route::post('/', [PaisController::class, 'store']);
    Route::get('/{id}', [PaisController::class, 'show']);
    Route::put('/{id}', [PaisController::class, 'update']);
    Route::delete('/{id}', [PaisController::class, 'destroy']);
});

// Departamentos
Route::prefix('departamentos')->group(function () {
    Route::get('/', [DepartamentoController::class, 'index']);
    Route::post('/', [DepartamentoController::class, 'store']);
    Route::get('/{id}', [DepartamentoController::class, 'show']);
    Route::put('/{id}', [DepartamentoController::class, 'update']);
    Route::delete('/{id}', [DepartamentoController::class, 'destroy']);
});

// Departamentos por país
Route::get('/paises/{id_pais}/departamentos', [DepartamentoController::class, 'byPais']);

// Ciudades
Route::prefix('ciudades')->group(function () {
    Route::get('/', [CiudadController::class, 'index']);
    Route::post('/', [CiudadController::class, 'store']);
    Route::get('/{id}', [CiudadController::class, 'show']);
    Route::put('/{id}', [CiudadController::class, 'update']);
    Route::delete('/{id}', [CiudadController::class, 'destroy']);
    // Ciudades por codigo postal
    Route::get('/codigo-postal/{codigo_postal}', [CiudadController::class, 'byCodigoPostal']);
});

// Ciudades por departamento
Route::get('/departamentos/{id_departamento}/ciudades', [CiudadController::class, 'byDepartamento']);

// ============================================
// RUTAS DE ESTADOS
// ============================================

// Estados (generales)
Route::prefix('estados')->group(function () {
    Route::get('/count', [EstadoController::class, 'withCount']);
    Route::get('/', [EstadoController::class, 'index']);
    Route::post('/', [EstadoController::class, 'store']);
    Route::get('/{id}', [EstadoController::class, 'show']);
    Route::put('/{id}', [EstadoController::class, 'update']);
    Route::delete('/{id}', [EstadoController::class, 'destroy']);
});

// Estados de inmuebles
Route::prefix('estados-inmueble')->group(function () {
    Route::get('/with-count', [EstadoInmuebleController::class, 'withCount']);
    Route::get('/estadisticas', [EstadoInmuebleController::class, 'estadisticas']);
    Route::get('/', [EstadoInmuebleController::class, 'index']);
    Route::post('/', [EstadoInmuebleController::class, 'store']);
    Route::get('/{id}', [EstadoInmuebleController::class, 'show']);
    Route::put('/{id}', [EstadoInmuebleController::class, 'update']);
    Route::delete('/{id}', [EstadoInmuebleController::class, 'destroy']);
});

// ============================================
// RUTAS DE RECURSOS HUMANOS
// ============================================

// Cargos
Route::prefix('cargos')->group(function () {
    Route::get('/with-count', [CargoController::class, 'withCount']);
    Route::get('/', [CargoController::class, 'index']);
    Route::post('/', [CargoController::class, 'store']);
    Route::get('/{id}', [CargoController::class, 'show']);
    Route::put('/{id}', [CargoController::class, 'update']);
    Route::delete('/{id}', [CargoController::class, 'destroy']);
    Route::get('/{id}/empleados', [CargoController::class, 'empleados']);
});

// Dependencias
Route::prefix('dependencias')->group(function () {
    Route::get('/with-count', [DependenciaController::class, 'withCount']);
    Route::get('/', [DependenciaController::class, 'index']);
    Route::post('/', [DependenciaController::class, 'store']);
    Route::get('/{id}', [DependenciaController::class, 'show']);
    Route::put('/{id}', [DependenciaController::class, 'update']);
    Route::delete('/{id}', [DependenciaController::class, 'destroy']);
    Route::get('/{id}/empleados', [DependenciaController::class, 'empleados']);
    Route::get('/{id}/estadisticas', [DependenciaController::class, 'estadisticas']);
});

// ============================================
// RUTAS DE TIPOS Y CLASIFICACIONES
// ============================================

// Tipos de apartamento
Route::prefix('tipos-apartamento')->group(function () {
    Route::get('/with-count', [TipoApartamentoController::class, 'withCount']);
    Route::get('/filtrar', [TipoApartamentoController::class, 'filtrar']);
    Route::get('/', [TipoApartamentoController::class, 'index']);
    Route::post('/', [TipoApartamentoController::class, 'store']);
    Route::get('/{id}', [TipoApartamentoController::class, 'show']);
    Route::put('/{id}', [TipoApartamentoController::class, 'update']);
    Route::delete('/{id}', [TipoApartamentoController::class, 'destroy']);
    Route::get('/{id}/calcular-valor', [TipoApartamentoController::class, 'calcularValor']);
});

// ============================================
// RUTAS DE UBICACIONES
// ============================================

Route::prefix('ubicaciones')->group(function () {
    Route::get('/by-barrio', [UbicacionController::class, 'byBarrio']);
    Route::get('/by-direccion', [UbicacionController::class, 'buscarPorDireccion']);
    Route::get('/{id}/ubicacion-completa', [UbicacionController::class, 'ubicacionCompleta']);
    Route::get('/', [UbicacionController::class, 'index']);
    Route::post('/', [UbicacionController::class, 'store']);
    Route::get('/{id}', [UbicacionController::class, 'show']);
    Route::put('/{id}', [UbicacionController::class, 'update']);
    Route::delete('/{id}', [UbicacionController::class, 'destroy']);
});

// Ubicaciones por ciudad
Route::get('/ciudades/{id_ciudad}/ubicaciones', [UbicacionController::class, 'byCiudad']);

// ============================================
// RUTAS DE PROYECTOS
// ============================================

Route::prefix('proyectos')->group(function () {
    Route::get('/estadisticas', [ProyectoController::class, 'estadisticas']);
    Route::get('/by-estado/{id_estado}', [ProyectoController::class, 'byEstado']);
    Route::get('/by-ciudad/{id_ciudad}', [ProyectoController::class, 'byCiudad']);
    Route::get('/buscar', [ProyectoController::class, 'buscar']);
    Route::get('/', [ProyectoController::class, 'index']);
    Route::post('/', [ProyectoController::class, 'store']);
    Route::get('/{id}', [ProyectoController::class, 'show']);
    Route::put('/{id}', [ProyectoController::class, 'update']);
    Route::delete('/{id}', [ProyectoController::class, 'destroy']);
    Route::get('/{id}/resumen', [ProyectoController::class, 'resumen']);

    // Rutas relacionadas con proyectos
    Route::get('/{id_proyecto}/torres', [TorreController::class, 'byProyecto']);
    Route::get('/{id_proyecto}/apartamentos', [ApartamentoController::class, 'byProyecto']);
    Route::get('/{id_proyecto}/locales', [LocalController::class, 'byProyecto']);
    Route::get('/{id_proyecto}/zonas-sociales', [ZonaSocialController::class, 'byProyecto']);
    Route::get('/{id_proyecto}/zonas-sociales/estadisticas', [ZonaSocialController::class, 'estadisticasPorProyecto']);
    Route::get('/{id_proyecto}/politicas-comision', [PoliticaComisionController::class, 'byProyecto']);
    Route::get('/{id_proyecto}/politicas-comision/vigentes', [PoliticaComisionController::class, 'vigentesPorProyecto']);
    Route::get('/{id_proyecto}/politicas-precio', [PoliticaPrecioProyectoController::class, 'byProyecto']);
    Route::get('/{id_proyecto}/apartamentos/estadisticas', [ApartamentoController::class, 'estadisticasPorProyecto']);
    Route::get('/{id_proyecto}/apartamentos/disponibles', [ApartamentoController::class, 'disponiblesPorProyecto']);
    Route::get('/{id_proyecto}/locales/estadisticas', [LocalController::class, 'estadisticasPorProyecto']);
    Route::get('/{id_proyecto}/locales/disponibles', [LocalController::class, 'disponiblesPorProyecto']);
});

// Proyectos por estado
Route::get('/estados/{id_estado}/proyectos', [ProyectoController::class, 'byEstado']);

// Proyectos por ciudad
Route::get('/ciudades/{id_ciudad}/proyectos', [ProyectoController::class, 'byCiudad']);

// ============================================
// RUTAS DE TORRES
// ============================================

Route::prefix('torres')->group(function () {
    Route::get('/', [TorreController::class, 'index']);
    Route::post('/', [TorreController::class, 'store']);
    Route::get('/buscar', [TorreController::class, 'buscar']);
    Route::get('/by-proyecto/{id_proyecto}', [TorreController::class, 'estadisticasPorProyecto']);
    Route::get('/by-estado/{id_estado}', [TorreController::class, 'byEstado']);

    Route::get('/by-proyecto/{id_proyecto}', [TorreController::class, 'byProyecto']);
    Route::get('/{id}/resumen', [TorreController::class, 'resumen']);

    // Rutas relacionadas con torres
    Route::get('/{id_torre}/apartamentos', [ApartamentoController::class, 'byTorre']);
    Route::get('/{id_torre}/locales', [LocalController::class, 'byTorre']);
    Route::get('/{id_torre}/pisos', [PisoTorreController::class, 'byTorre']);

    Route::get('/{id}', [TorreController::class, 'show'])->whereNumber('id');
    Route::put('/{id}', [TorreController::class, 'update'])->whereNumber('id');
    Route::delete('/{id}', [TorreController::class, 'destroy'])->whereNumber('id');
});

// ============================================
// RUTAS DE PISOS
// ============================================

Route::prefix('pisos')->group(function () {
    Route::get('/', [PisoTorreController::class, 'index']);
    Route::post('/', [PisoTorreController::class, 'store']);
    Route::get('/by-uso', [PisoTorreController::class, 'byUso']);
    Route::post('/crear-multiples', [PisoTorreController::class, 'crearMultiples']);

    Route::get('/by-torre/{id_torre}', [PisoTorreController::class, 'estadisticasPorTorre']);

    Route::get('/{id_torre}', [PisoTorreController::class, 'byTorre'])->whereNumber('id_torre');
    Route::get('/{id}/resumen', [PisoTorreController::class, 'resumen']);

    // Rutas relacionadas con pisos
    Route::get('/{id_piso_torre}/apartamentos', [ApartamentoController::class, 'byPiso']);
    Route::get('/{id_piso_torre}/locales', [LocalController::class, 'byPiso']);

    Route::get('/{id}', [PisoTorreController::class, 'show'])->whereNumber('id');
    Route::put('/{id}', [PisoTorreController::class, 'update'])->whereNumber('id');
    Route::delete('/{id}', [PisoTorreController::class, 'destroy'])->whereNumber('id');
});

// ============================================
// RUTAS DE APARTAMENTOS
// ============================================

Route::prefix('apartamentos')->group(function () {
    Route::get('/', [ApartamentoController::class, 'index']);
    Route::post('/', [ApartamentoController::class, 'store']);
    Route::get('/{id}', [ApartamentoController::class, 'show']);
    Route::put('/{id}', [ApartamentoController::class, 'update']);
    Route::delete('/{id}', [ApartamentoController::class, 'destroy']);
    Route::post('/buscar', [ApartamentoController::class, 'buscar']);
    Route::get('/{id}/resumen', [ApartamentoController::class, 'resumen']);
    Route::patch('/{id}/cambiar-estado', [ApartamentoController::class, 'cambiarEstado']);

    // Rutas relacionadas con apartamentos
    Route::get('/{id_apartamento}/parqueaderos', [ParqueaderoController::class, 'byApartamento']);
});

// Apartamentos por tipo
Route::get('/tipos-apartamento/{id_tipo}/apartamentos', [ApartamentoController::class, 'byTipo']);

// Apartamentos por estado
Route::get('/estados-inmueble/{id_estado}/apartamentos', [ApartamentoController::class, 'byEstado']);

// ============================================
// RUTAS DE LOCALES
// ============================================

Route::prefix('local')->group(function () {
    Route::get('/', [LocalController::class, 'index']);
    Route::post('/', [LocalController::class, 'store']);
    Route::get('/buscar', [LocalController::class, 'buscar']);
    Route::get('/by-estado/{id_estado_inmueble}', [LocalController::class, 'byEstado']);
    Route::get('/by-piso/{id_piso_torre}', [LocalController::class, 'byPiso']);
    Route::get('/by-proyecto/{id_proyecto}', [LocalController::class, 'byProyecto']);
    Route::get('/by-rango-area', [LocalController::class, 'byRangoArea']);
    Route::get('/by-torre/{id_torre}', [LocalController::class, 'byTorre']);
    Route::patch('/cambiar-estado/{id}', [LocalController::class, 'cambiarEstado']);
    Route::get('/disponibles/proyecto/{id_proyecto}', [LocalController::class, 'disponiblesPorProyecto']);
    Route::get('/estadisticas/proyecto/{id_proyecto}', [LocalController::class, 'estadisticasPorProyecto']);
    Route::get('/resumen/{id}', [LocalController::class, 'resumen']);

    Route::get('/{id}', [LocalController::class, 'show'])->whereNumber('id');
    Route::put('/{id}', [LocalController::class, 'update'])->whereNumber('id');
    Route::delete('/{id}', [LocalController::class, 'destroy'])->whereNumber('id');
});

// ============================================
// RUTAS DE PARQUEADEROS
// ============================================

Route::prefix('parqueaderos')->group(function () {
    Route::get('/', [ParqueaderoController::class, 'index']);
    Route::post('/', [ParqueaderoController::class, 'store']);
    Route::get('/{id}', [ParqueaderoController::class, 'show']);
    Route::put('/{id}', [ParqueaderoController::class, 'update']);
    Route::delete('/{id}', [ParqueaderoController::class, 'destroy']);
    Route::get('/tipo/{tipo}', [ParqueaderoController::class, 'byTipo']);
    Route::get('/disponibles', [ParqueaderoController::class, 'disponibles']);
    Route::get('/asignados', [ParqueaderoController::class, 'asignados']);
    Route::patch('/{id}/asignar', [ParqueaderoController::class, 'asignar']);
    Route::patch('/{id}/desasignar', [ParqueaderoController::class, 'desasignar']);
    Route::get('/buscar', [ParqueaderoController::class, 'buscar']);
    Route::get('/{id}/resumen', [ParqueaderoController::class, 'resumen']);
    Route::get('/estadisticas/generales', [ParqueaderoController::class, 'estadisticas']);
    Route::get('/disponibles/{tipo}', [ParqueaderoController::class, 'disponiblesPorTipo']);
    Route::post('/crear-multiples', [ParqueaderoController::class, 'crearMultiples']);
});

// ============================================
// RUTAS DE ZONAS SOCIALES
// ============================================

Route::prefix('zonas-sociales')->group(function () {
    Route::get('/', [ZonaSocialController::class, 'index']);
    Route::post('/', [ZonaSocialController::class, 'store']);
    Route::get('/{id}', [ZonaSocialController::class, 'show']);
    Route::put('/{id}', [ZonaSocialController::class, 'update']);
    Route::delete('/{id}', [ZonaSocialController::class, 'destroy']);
    Route::get('/buscar', [ZonaSocialController::class, 'buscar']);
    Route::get('/{id}/resumen', [ZonaSocialController::class, 'resumen']);
    Route::post('/crear-multiples', [ZonaSocialController::class, 'crearMultiples']);
    Route::get('/estadisticas/generales', [ZonaSocialController::class, 'estadisticas']);
    Route::get('/comunes', [ZonaSocialController::class, 'zonasComunes']);
});

// ============================================
// RUTAS DE POLÍTICAS DE COMISIÓN
// ============================================

Route::prefix('politicas-comision')->group(function () {
    Route::get('/', [PoliticaComisionController::class, 'index']);
    Route::post('/', [PoliticaComisionController::class, 'store']);
    Route::get('/{id}', [PoliticaComisionController::class, 'show']);
    Route::put('/{id}', [PoliticaComisionController::class, 'update']);
    Route::delete('/{id}', [PoliticaComisionController::class, 'destroy']);
    Route::get('/vigentes', [PoliticaComisionController::class, 'vigentes']);
    Route::get('/vencidas', [PoliticaComisionController::class, 'vencidas']);
    Route::get('/aplica-a/{aplica_a}', [PoliticaComisionController::class, 'byAplicaA']);
    Route::get('/base-calculo/{base_calculo}', [PoliticaComisionController::class, 'byBaseCalculo']);
    Route::post('/{id}/calcular', [PoliticaComisionController::class, 'calcularComision']);
    Route::get('/estadisticas', [PoliticaComisionController::class, 'estadisticas']);
    Route::get('/{id}/resumen', [PoliticaComisionController::class, 'resumen']);
    Route::get('/buscar', [PoliticaComisionController::class, 'buscar']);
});

// ============================================
// RUTAS DE POLÍTICAS DE PRECIO
// ============================================

Route::prefix('politicas-precio')->group(function () {
    Route::get('/', [PoliticaPrecioProyectoController::class, 'index']);
    Route::post('/', [PoliticaPrecioProyectoController::class, 'store']);
    Route::get('/activas', [PoliticaPrecioProyectoController::class, 'activas']);
    Route::get('/inactivas', [PoliticaPrecioProyectoController::class, 'inactivas']);

    Route::get('/proyecto/{id_proyecto}', [PoliticaPrecioProyectoController::class, 'vigentePorProyecto']);

    Route::patch('/cambiar-estado/{id}', [PoliticaPrecioProyectoController::class, 'cambiarEstado']);

    Route::post('/{id}/calcular', [PoliticaPrecioProyectoController::class, 'calcularPrecio']);
    Route::get('/{id}/resumen', [PoliticaPrecioProyectoController::class, 'resumen']);
    Route::get('/estadisticas', [PoliticaPrecioProyectoController::class, 'estadisticas']);

    Route::get('/{id}', [PoliticaPrecioProyectoController::class, 'show'])->whereNumber('id');
    Route::put('/{id}', [PoliticaPrecioProyectoController::class, 'update'])->whereNumber('id');
    Route::delete('/{id}', [PoliticaPrecioProyectoController::class, 'destroy'])->whereNumber('id');
});
