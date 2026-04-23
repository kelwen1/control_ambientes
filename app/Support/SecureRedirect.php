<?php

namespace App\Support;

/**
 * Evita redirecciones abiertas (open redirect) cuando se acepta una URL de retorno desde el cliente.
 */
class SecureRedirect
{
    /**
     * @param  string  $defaultRouteName  Ruta nombrada de Laravel si el candidato no es seguro.
     */
    public static function safeUrl(?string $candidate, string $defaultRouteName = 'dashboard'): string
    {
        $default = route($defaultRouteName);
        if ($candidate === null) {
            return $default;
        }
        $c = trim($candidate);
        if ($c === '') {
            return $default;
        }
        if (str_starts_with($c, '//')) {
            return $default;
        }
        if (str_starts_with($c, '/')) {
            return url($c);
        }
        $appRoot = rtrim((string) config('app.url'), '/');
        if ($appRoot !== '' && str_starts_with($c, $appRoot)) {
            return $c;
        }
        if (preg_match('#^https?://#i', $c)) {
            $host = parse_url($c, PHP_URL_HOST);
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
            if ($host && $appHost && strcasecmp((string) $host, (string) $appHost) === 0) {
                return $c;
            }

            return $default;
        }

        return $default;
    }
}
