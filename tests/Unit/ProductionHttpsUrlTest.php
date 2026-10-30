<?php

namespace Tests\Unit;

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ProductionHttpsUrlTest extends TestCase
{
    public function test_production_generates_https_asset_and_route_urls(): void
    {
        $previousEnvironment = $this->app->environment();

        try {
            $this->app->instance('env', 'production');
            (new AppServiceProvider($this->app))->boot();

            $this->assertStringStartsWith('https://', asset('build/assets/app.css'));
            $this->assertStringStartsWith('https://', route('login'));
        } finally {
            URL::forceScheme(null);
            $this->app->instance('env', $previousEnvironment);
        }
    }
}
