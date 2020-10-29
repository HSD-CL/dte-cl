<?php

namespace HSDCL\DteCl\Tests\Unit;

use Illuminate\Support\Facades\File;
use HSDCL\DteCl\Tests\TestCase;
use Illuminate\Support\Str;

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
        $file = '/tmp/' . Str::uuid() . '.xml';
        // make sure we're starting from a clean state
        if (File::exists($file)) {
            unlink($file);
        }
        $this->assertFalse(File::exists($file));
        $this->artisan('dte:purchase-book-certification', [
            '--firma'     => base_path() . '/../../../../resources/assets/certs/cert.pfx',
            '--source'    => base_path() . '/../../../../resources/assets/set_pruebas/002-compras.csv',
            '--output'    => $file,
            '--pass'      => 'Aaraneda1*'
        ])->assertExitCode(0);

        $this->assertTrue(File::exists($file));
    }
}
