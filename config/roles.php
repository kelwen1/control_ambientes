<?php

return [
    /*
    | Roles del sistema (tabla rol). Orden: administrador, coordinación mayor, coordinación, instructor.
    | La tabla rol debe tener estos id_rol con el campo 'rol' indicado.
    */
    'ids' => [
        'administrador'   => 1,
        'coordinacion_L'  => 2,  // coordinación mayor (la de mayor nivel)
        'coordinacion'    => 3,
        'instructor'       => 4,
    ],

    'names' => [
        1 => 'administrador',
        2 => 'coordinacion_L',
        3 => 'coordinacion',
        4 => 'instructor',
    ],
];
