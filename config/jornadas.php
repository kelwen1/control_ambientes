<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Horarios por jornada (reservas y disponibilidad)
    |--------------------------------------------------------------------------
    | Lunes a viernes: Mañana 7-13, Tarde 13-19, Noche 19-22.
    | Sábados y domingos: una sola jornada 7 am - 5 pm (todo el día, una reserva por ambiente).
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
    'fin_semana' => [
        'inicio' => '07:00',
        'fin'    => '17:00',
        'label'  => 'Todo el día (7 am - 5 pm)',
    ],
];
