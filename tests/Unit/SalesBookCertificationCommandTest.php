<?php

namespace HSDCL\DteCl\Tests\Unit;

use Illuminate\Support\Facades\File;
use HSDCL\DteCl\Tests\TestCase;
use Illuminate\Support\Str;

/**
 * Class ExampleTest
 * @package HSDCL\DteCl\Tests
 * @author  Danilo Vasquez <dvasquezr.ko@gmail.com>
 */
class SalesBookCertificationCommandTest extends TestCase
{
    /**
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function setUp(): void
    {
        parent::setUp();
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
        $dotenv->load();
    }

    /**
     * @test
     * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
     */
    public function canCallCommand()
    {
        $file = '/tmp/' . Str::uuid() . '.xml';
        // make sure we're starting from a clean state
        if (File::exists($file)) {
            unlink($file);
        }
        $this->assertFalse(File::exists($file));
        $this->artisan('dte:sales-book-certification', [
            '--firma'             => __DIR__ . '/../../resources/assets/certs/cert.pfx',
            '--source'            => base_path() . '/../../../../resources/assets/set_pruebas/libro_ventas.csv',
            '--output'            => $file,
            '--pass'              => env('FIRMA_PASS'),
            '--RutEmisorLibro'    => env('RUTEmisor'),
            '--RutEnvia'          => env('RutEnvia'),
            '--PeriodoTributario' => '1980-02',
            '--FchResol'          => env('FechaResolucion'),
            '--NroResol'          => env('NroResol'),
            '--TipoOperacion'     => 'VENTA',
            '--TipoLibro'         => 'ESPECIAL',
            '--TipoEnvio'         => 'TOTAL',
            '--FolioNotificacion' => env('FolioNotificacion'),
        ])->assertExitCode(0);

        $this->assertTrue(File::exists($file));
    }
}
