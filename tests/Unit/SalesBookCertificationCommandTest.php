<?php

namespace HSDCL\DteCl\Tests\Unit;

use Illuminate\Support\Facades\File;
use HSDCL\DteCl\Tests\TestCase;
use Illuminate\Support\Str;

/**
 * Class ExampleTest
 * @package HSDCL\DteCl\Tests
 * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
 */
class SalesBookCertificationCommandTest extends TestCase
{
    /**
     * @test
     * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
     */
    public function canCallCommand()
    {
        $file = 'xml/' . Str::uuid() . '.xml';
        // make sure we're starting from a clean state
        if (File::exists($file)) {
            unlink($file);
        }
        $this->assertFalse(File::exists($file));
        $this->artisan('dte:sales-book-certification', [
            '--firma'               => base_path() . '/../../../../resources/assets/certs/danilo.p12',
            '--source'              => base_path() . '/../../../../resources/assets/set_pruebas/libro_ventas2.csv',
            '--output'              => $file,
            '--pass'                => 'Aaraneda1*',
            '--RutEmisorLibro'      => '78465260-2',
            '--RutEnvia'            => '15751871-2',
            '--PeriodoTributario'   => '1980-02',
            '--FchResol'            => '2020-07-27',
            '--NroResol'            => 102006,
            '--TipoOperacion'       => 'VENTA',
            '--TipoLibro'           => 'ESPECIAL',
            '--TipoEnvio'           => 'TOTAL',
            '--FolioNotificacion'   => 102006,
        ])->assertExitCode(0);

        $this->assertTrue(File::exists($file));
    }
}
