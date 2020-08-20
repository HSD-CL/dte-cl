<?php

namespace HSDCL\DteCl\Tests;

use Orchestra\Testbench\TestCase;
use HSDCL\DteCl\DteClServiceProvider;

/**
 * Class ExampleTest
 * @package HSDCL\DteCl\Tests
 * @author David Lopez <dleo.lopez@gmail.com>
 */
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
