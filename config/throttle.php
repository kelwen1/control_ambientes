<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Límites de tasa (rate limiting)
    |--------------------------------------------------------------------------
    | Formato: 'intentos por minuto' (decayMinutes = 1).
    | Aumenta estos valores si ves "Too Many Requests" (429) con uso normal.
    | En producción puedes bajar los valores por seguridad.
    */
    'login_get'   => env('THROTTLE_LOGIN_GET', 20),   // peticiones GET a /login por minuto
    'login_post'  => env('THROTTLE_LOGIN_POST', 10),  // intentos de login (POST) por minuto
    'write'       => env('THROTTLE_WRITE', 60),       // crear/actualizar (fichas, reservas, inventario) por minuto
    'destroy'     => env('THROTTLE_DESTROY', 20),     // eliminar (fichas, reservas, inventario) por minuto
    'users'       => env('THROTTLE_USERS', 20),       // crear/actualizar usuarios por minuto
    'users_destroy' => env('THROTTLE_USERS_DESTROY', 10), // eliminar usuarios por minuto
];
