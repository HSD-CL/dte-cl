<?php

namespace HSDCL\DteCl\Tests\Unit;

use Illuminate\Support\Facades\File;
use HSDCL\DteCl\Tests\TestCase;
use Illuminate\Support\Str;
//use \sasco\LibreDTE\Sii\Folios;

/**
 * Class ExampleTest
 * @package HSDCL\DteCl\Tests
 * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
 * @author David Lopez <dlopez@hsd.cl>
 */
class ExportCertificactionCommandTest extends TestCase
{
    /**
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function setUp(): void
    {
        parent::setUp();
        $dotenv = \Dotenv\Dotenv::create(__DIR__ . '/../..');
        $dotenv->load();
    }

    /**
     * @test
     * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function canCallCommand()
    {
        $fileName = '/tmp/' . Str::uuid() . '.xml';
        // make sure we're starting from a clean state
        if (File::exists($fileName)) {
            unlink($fileName);
        }
        $this->assertFalse(File::exists($fileName));
        $this->artisan('dte:export-certification', [
            '--folios-fe'      => __DIR__ . '/../../resources/assets/xml/folios/110.xml',
            '--folios-nc'      => __DIR__ . '/../../resources/assets/xml/folios/112.xml',
            '--folios-nd'      => __DIR__ . '/../../resources/assets/xml/folios/111.xml',
            '--firma'          => __DIR__ . '/../../resources/assets/certs/cert.pfx',
            '--source'         => __DIR__ . '/../../resources/assets/set_pruebas/009-set_exportacion_1.txt',
            '--output'         => $fileName,
            '--pass'           => env('FIRMA_PASS'),
            '--start-folio-fe' => '2',
            '--start-folio-nd' => '2',
            '--start-folio-nc' => '2',
            '--resolucion'     => env('FechaResolucion'),
            '--RUTEmisor'      => env('RUTEmisor'),
            '--RznSoc'         => env('RznSoc'),
            '--GiroEmis'       => env('GiroEmis'),
            '--Acteco'         => env('Acteco'),
            '--DirOrigen'      => env('DirOrigen'),
            '--CmnaOrigen'     => env('CmnaOrigen'),
            '--RUTRecep'       => '55555555-5',
            '--RznSocRecep'    => 'Extranjero',
            '--GiroRecep'      => 'Extranjero',
            '--DirRecep'       => 'China',
            '--CmnaRecep'      => env('CmnaRecep'),
            '--RutEnvia'       => env('RutEnvia'),
            '--RutReceptor'    => env('RutReceptor'),
        ])->assertExitCode(0);

        $this->assertTrue(File::exists($fileName));
    }

    /**
     * @test
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function canCallCommandSetIi()
    {
        $fileName = '/tmp/' . Str::uuid() . '.xml';
        // make sure we're starting from a clean state
        if (File::exists($fileName)) {
            unlink($fileName);
        }
        $this->assertFalse(File::exists($fileName));
        $this->artisan('dte:export-certification', [
            '--folios-fe'      => __DIR__ . '/../../resources/assets/xml/folios/110.xml',
            '--folios-nc'      => __DIR__ . '/../../resources/assets/xml/folios/112.xml',
            '--folios-nd'      => __DIR__ . '/../../resources/assets/xml/folios/111.xml',
            '--firma'          => __DIR__ . '/../../resources/assets/certs/cert.pfx',
            '--source'         => __DIR__ . '/../../resources/assets/set_pruebas/010-set_exportacion_2.txt',
            '--output'         => $fileName,
            '--pass'           => env('FIRMA_PASS'),
            '--start-folio-fe' => '3',
            '--start-folio-nd' => '3',
            '--start-folio-nc' => '3',
            '--resolucion'     => env('FechaResolucion'),
            '--RUTEmisor'      => env('RUTEmisor'),
            '--RznSoc'         => env('RznSoc'),
            '--GiroEmis'       => env('GiroEmis'),
            '--Acteco'         => env('Acteco'),
            '--DirOrigen'      => env('DirOrigen'),
            '--CmnaOrigen'     => env('CmnaOrigen'),
            '--RUTRecep'       => '55555555-5',
            '--RznSocRecep'    => 'Extranjero',
            '--GiroRecep'      => 'Extranjero',
            '--DirRecep'       => 'China',
            '--CmnaRecep'      => env('CmnaRecep'),
            '--RutEnvia'       => env('RutEnvia'),
            '--RutReceptor'    => env('RutReceptor'),
        ])->assertExitCode(0);

        $this->assertTrue(File::exists($fileName));
    }
}
