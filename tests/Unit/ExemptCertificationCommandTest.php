<?php

namespace HSDCL\DteCl\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use HSDCL\DteCl\Tests\TestCase;
use Illuminate\Support\Str;

/**
 * Class ExampleTest
 * @package HSDCL\DteCl\Tests
 * @author David Lopez <dleo.lopez@gmail.com>
 */
class ExemptCertificationCommandTest extends TestCase
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
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function canCallCommand()
    {
        $fileName = '/tmp/' . Str::uuid() . '.xml';
        // make sure we're starting from a clean state
        if (File::exists($fileName)) {
            unlink($fileName);
        }
        $this->assertFalse(File::exists($fileName));
        $this->artisan('dte:exempt-certification', [
            '--folios-fe'   => __DIR__ . '/../../resources/assets/xml/folios/34.xml',
            '--folios-nc'   => __DIR__ . '/../../resources/assets/xml/folios/61.xml',
            '--folios-nd'   => __DIR__ . '/../../resources/assets/xml/folios/56.xml',
            '--firma'       => __DIR__ . '/../../resources/assets/certs/cert.pfx',
            '--source'      => __DIR__ . '/../../resources/assets/set_pruebas/004-factura_exenta.txt',
            '--output'      => $fileName,
            '--pass'        => env('FIRMA_PASS'),
            '--start-fe'    => '51',
            '--start-nd'    => '52',
            '--start-nc'    => '54',
            '--resolucion'  => env('FechaResolucion'),
            '--RUTEmisor'   => env('RUTEmisor'),
            '--RznSoc'      => env('RznSoc'),
            '--GiroEmis'    => env('GiroEmis'),
            '--Acteco'      => env('Acteco'),
            '--DirOrigen'   => env('DirOrigen'),
            '--CmnaOrigen'  => env('CmnaOrigen'),
            '--RUTRecep'    => env('RUTRecep'),
            '--RznSocRecep' => env('RznSocRecep'),
            '--GiroRecep'   => env('GiroRecep'),
            '--DirRecep'    => env('DirRecep'),
            '--CmnaRecep'   => env('CmnaRecep'),
            '--RutEnvia'    => env('RutEnvia'),
            '--RutReceptor' => env('RutReceptor'),
        ])->assertExitCode(0);

        $this->assertTrue(File::exists($fileName));
    }
}
