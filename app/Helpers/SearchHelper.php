<?php

namespace App\Helpers;

class SearchHelper
{
    /**
     * Escapa caracteres especiales de LIKE para prevenir inyección SQL indirecta
     * 
     * @param string $search
     * @return string
     */
    public static function escapeLikeSpecialChars(string $search): string
    {
        // Escapar caracteres especiales de LIKE: %, _, \
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
        return $escaped;
    }
}

