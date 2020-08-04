<?php

namespace HSDCL\DteCl\Tests;

use Orchestra\Testbench\TestCase;
use HSDCL\DteCl\DteClServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [DteClServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
