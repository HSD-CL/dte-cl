<?php

namespace HSDCL\DteCl\Tests\Unit;

use Illuminate\Support\Facades\File;
use HSDCL\DteCl\Tests\TestCase;

/**
 * Class ExampleTest
 * @package HSDCL\DteCl\Tests
 * @author David Lopez <dleo.lopez@gmail.com>
 */
class PurchaseBookCertificationCommandTest extends TestCase
{
    /**
     * @test
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function canCallCommand()
    {
        // make sure we're starting from a clean state
        if (File::exists('/tmp/file.xml')) {
            unlink('/tmp/file.xml');
        }
        $this->assertFalse(File::exists('/tmp/file.xml'));
        $this->artisan('dte:purchase-book-certification', [
            '--firma'     => base_path() . '/../../../../resources/assets/certs/cert.pfx',
            '--source'    => base_path() . '/../../../../resources/assets/set_pruebas/003-compras.txt',
            '--output'    => '/tmp/file.xml',
            '--pass'      => env('PASS')
        ])->assertExitCode(0);

        $this->assertTrue(File::exists('/tmp/file.xml'));
    }
}
