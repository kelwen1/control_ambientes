<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Horarios por jornada (reservas y disponibilidad)
    |--------------------------------------------------------------------------
    | Mañana: 7 am - 1 pm | Tarde: 1 pm - 7 pm | Noche: 6 pm - 10 pm
    */
    'manana' => [
        'inicio' => '07:00',
        'fin'    => '13:00',
        'label'  => 'Mañana (7 am - 1 pm)',
    ],
    'tarde' => [
        'inicio' => '13:00',
        'fin'    => '19:00',
        'label'  => 'Tarde (1 pm - 7 pm)',
    ],
    'noche' => [
        'inicio' => '19:00',
        'fin'    => '22:00',
        'label'  => 'Noche (7 pm - 10 pm)',
    ],
];
