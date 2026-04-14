<?php return array (
  'App\\Providers\\EventServiceProvider' => 
  array (
  ),
  'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider' => 
  array (
    'App\\Events\\VentaCreada' => 
    array (
      0 => 'App\\Listeners\\EnviarNotificacionesVenta@handle',
    ),
    'Illuminate\\Auth\\Events\\Login' => 
    array (
      0 => 'App\\Listeners\\LogSuccessfulLogin@handle',
    ),
  ),
);