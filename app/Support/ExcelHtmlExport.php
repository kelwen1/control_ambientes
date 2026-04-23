<?php

namespace App\Support;

use Illuminate\Http\Response;

/**
 * Exporta una vista Blade con tablas HTML como .xls para abrir en Excel
 * (sin extensión php-zip). Incluye BOM UTF-8 para tildes y ñ.
 */
class ExcelHtmlExport
{
    public static function download(string $view, array $data, string $filenamePrefix): Response
    {
        $html = view($view, $data)->render();
        $filename = $filenamePrefix.'_'.date('Y-m-d_His').'.xls';

        return response("\xEF\xBB\xBF".$html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
