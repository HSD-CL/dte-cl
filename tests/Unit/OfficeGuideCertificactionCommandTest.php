<?php

namespace HSDCL\DteCl\Tests\Unit;

use Illuminate\Support\Facades\File;
use HSDCL\DteCl\Tests\TestCase;
use Illuminate\Support\Str;

//use \sasco\LibreDTE\Sii\Folios;

/**
 * Class ExampleTest
 * @package HSDCL\DteCl\Tests
 * @author  Danilo Vasquez <dvasquezr.ko@gmail.com>
 */
class OfficeGuideCertificactionCommandTest extends TestCase
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
        //$folios = file_get_contents(base_path() . '/../../../../resources/assets/xml/folios/52.xml');
        $file = '/tmp/' . Str::uuid() . '.xml';
        // make sure we're starting from a clean state
        if (File::exists($file)) {
            unlink($file);
        }
        $this->assertFalse(File::exists($file));
        $this->artisan('dte:office-guide-certification', [
            '--firma'       => base_path() . '/../../../../resources/assets/certs/cert.pfx',
            '--source'      => base_path() . '/../../../../resources/assets/set_pruebas/005-guia_despacho.txt',
            '--folios'      => base_path() . '/../../../../resources/assets/xml/folios/52.xml',
            '--output'      => $file,
            '--pass'        => env('FIRMA_PASS'),
            '--start-folio' => '51',
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

        $this->assertTrue(File::exists($file));
    }
}
