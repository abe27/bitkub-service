<?php

namespace Abe27\Bitkub\Tests;

use Abe27\Bitkub\Bitkub;
use Abe27\Bitkub\Exceptions\BitkubException;
use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Http;

class BitkubTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return ['Abe27\Bitkub\BitkubServiceProvider'];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Bitkub' => 'Abe27\Bitkub\Facades\Bitkub'
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('bitkub.api_key', 'test_api_key');
        $app['config']->set('bitkub.api_secret', 'test_api_secret');
        $app['config']->set('bitkub.endpoint', 'https://api.bitkub.com');
    }

    /** @test */
    public function it_can_get_status()
    {
        Http::fake([
            'https://api.bitkub.com/api/status' => Http::response([
                'error' => 0,
                'result' => [
                    'status' => 'ok'
                ]
            ], 200)
        ]);

        $status = app('bitkub')->status();

        $this->assertEquals(0, $status['error']);
        $this->assertEquals('ok', $status['result']['status']);

        Http::assertSent(function ($request) {
            return $request->url() == 'https://api.bitkub.com/api/status';
        });
    }

    /** @test */
    public function it_can_get_ticker()
    {
        Http::fake([
            'https://api.bitkub.com/api/market/ticker?sym=THB_BTC' => Http::response([
                'error' => 0,
                'result' => [
                    'THB_BTC' => [
                        'last' => 500000,
                        'high24hr' => 510000,
                        'low24hr' => 490000,
                    ]
                ]
            ], 200)
        ]);

        $ticker = app('bitkub')->ticker('THB_BTC');

        $this->assertEquals(0, $ticker['error']);
        $this->assertEquals(500000, $ticker['result']['THB_BTC']['last']);

        Http::assertSent(function ($request) {
            return $request->url() == 'https://api.bitkub.com/api/market/ticker?sym=THB_BTC';
        });
    }

    /** @test */
    public function it_handles_errors_properly()
    {
        Http::fake([
            'https://api.bitkub.com/api/market/balances' => Http::response([
                'error' => 1,
                'message' => 'Invalid API key'
            ], 401)
        ]);

        $this->expectException(BitkubException::class);
        $this->expectExceptionMessage('Invalid API key');

        app('bitkub')->balances();
    }
}
