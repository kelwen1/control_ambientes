<?php

namespace Tests\Unit;

use App\Helpers\DiaSemanaDesdeFechaHelper;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DiaSemanaDesdeFechaHelperTest extends TestCase
{
    #[Test]
    public function clave_desde_ymd_matches_known_weekdays(): void
    {
        $this->assertSame('miercoles', DiaSemanaDesdeFechaHelper::claveDesdeYmd('2026-04-08'));
        $this->assertSame('domingo', DiaSemanaDesdeFechaHelper::claveDesdeYmd('2026-04-05'));
    }

    #[Test]
    public function invalid_or_empty_returns_null(): void
    {
        $this->assertNull(DiaSemanaDesdeFechaHelper::claveDesdeYmd(null));
        $this->assertNull(DiaSemanaDesdeFechaHelper::claveDesdeYmd(''));
        $this->assertNull(DiaSemanaDesdeFechaHelper::claveDesdeYmd('no-es-fecha'));
    }
}
