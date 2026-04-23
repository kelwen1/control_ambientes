<?php

namespace Tests\Unit;

use App\Support\SecureRedirect;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SecureRedirectTest extends TestCase
{
    #[Test]
    public function null_or_empty_returns_dashboard_route(): void
    {
        $this->assertSame(route('dashboard'), SecureRedirect::safeUrl(null));
        $this->assertSame(route('dashboard'), SecureRedirect::safeUrl(''));
        $this->assertSame(route('dashboard'), SecureRedirect::safeUrl('   '));
    }

    #[Test]
    public function protocol_relative_url_is_rejected(): void
    {
        $this->assertSame(route('dashboard'), SecureRedirect::safeUrl('//evil.example/phishing'));
    }

    #[Test]
    public function external_http_url_is_rejected(): void
    {
        $this->assertSame(route('dashboard'), SecureRedirect::safeUrl('https://malicious.example/steal'));
    }

    #[Test]
    public function relative_path_same_app_is_accepted(): void
    {
        $out = SecureRedirect::safeUrl('/s/mi-jornada/miercoles');
        $this->assertStringContainsString('/s/mi-jornada/miercoles', $out);
        $this->assertStringStartsWith((string) config('app.url'), $out);
    }

    #[Test]
    public function absolute_url_same_host_is_accepted(): void
    {
        $root = rtrim((string) config('app.url'), '/');
        $out = SecureRedirect::safeUrl($root.'/s/');
        $this->assertSame($root.'/s/', $out);
    }
}
