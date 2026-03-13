<?php

namespace App\Helpers;

use Carbon\Carbon;

class FormatoHelper
{
    public static function money($value)
    {
        $num = intval(ceil(floatval($value ?? 0)));
        return '$' . number_format($num, 0, ',', '.');
    }

    public static function date($date)
    {
        if (!$date) return '—';
        try {
            return Carbon::parse($date)->format('d/m/Y');
        } catch (\Exception $e) {
            return '—';
        }
    }

    public static function safe($value)
    {
        return ($value === null || $value === '' || $value === 'null') ? '—' : (string) $value;
    }

    public static function getZonas($proyecto)
    {
        if (!$proyecto) return '—';
        
        $arr = $proyecto->zonas_sociales ?? 
               $proyecto->zonasSociales ?? 
               $proyecto->zonas_sociales_proyecto ?? 
               [];
               
        if (!is_array($arr) || empty($arr)) return '—';
        
        return collect($arr)
            ->map(fn($z) => $z->nombre ?? $z['nombre'] ?? null)
            ->filter()
            ->implode(', ');
    }

    public static function parqueaderoTexto($venta)
    {
        $a = $venta->apartamento;
        if (!$a) return 'No aplica (Local)';

        if ($a->parqueadero) {
            $numero = $a->parqueadero->numero ?? 
                      $a->parqueadero->codigo ?? 
                      $a->parqueadero->nombre ?? 
                      $a->parqueadero->id_parqueadero ?? '—';
            return "Sí ({$numero})";
        }

        if (is_array($a->parqueaderos) && count($a->parqueaderos)) {
            return 'Sí';
        }

        if (property_exists($a, 'tiene_parqueadero')) {
            return $a->tiene_parqueadero ? 'Sí' : 'No';
        }

        return 'No';
    }
}