<?php

namespace HSDCL\DteCl\Tests;

use HSDCL\DteCl\DteCl;
use Orchestra\Testbench\TestCase;
use HSDCL\DteCl\DteClServiceProvider;

/**
 * Class ExampleTest
 * @package HSDCL\DteCl\Tests
 * @author David Lopez <dleo.lopez@gmail.com>
 */
class DteClTest extends TestCase
{
    /**
     * @test
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function canLogin()
    {
        $dte = new DteCl();
        dd($dte->getLogin());
        $this->assertIsString($dte->getLogin());
    }
}
