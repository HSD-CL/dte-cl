<?php
namespace HSDCL\DteCl\Tests;

use HSDCL\DteCl\DteClServiceProvider;
use Orchestra\Testbench\TestCase as Test;

class TestCase extends Test
{
    public function setUp(): void
    {
        parent::setUp();
        // additional setup
    }

    protected function getPackageProviders($app)
    {
        return [
            DteClServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
    }
}
