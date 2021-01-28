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
 */
class ExportCertificactionCommandTest extends TestCase
{
    /**
     * @test
     * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
     */
    public function canCallCommand()
    {
        //$folios = file_get_contents(base_path() . '/../../../../resources/assets/xml/folios/52.xml');
        $file = 'xml/' . Str::uuid() . '.xml';
        // make sure we're starting from a clean state
        if (File::exists($file)) {
            unlink($file);
        }
        $this->assertFalse(File::exists($file));
        $this->artisan('dte:export-certification', [
            '--firma'           => base_path() . '/../../../../resources/assets/certs/cert.pfx',
            '--source'          => base_path() . '/../../../../resources/assets/set_pruebas/009-set_exportacion_a.txt',
            '--folios-fe'      => base_path() . '/../../../../resources/assets/xml/folios/110.xml',
            '--folios-nc'      => base_path() . '/../../../../resources/assets/xml/folios/111.xml',
            '--folios-nd'      => base_path() . '/../../../../resources/assets/xml/folios/112.xml',
            '--output'          => $file,
            '--pass'            => 'Aaraneda1*',
            '--start-folio-fe' => '1',
            '--start-folio-nc' => '1',
            '--start-folio-nd' => '1',
            '--resolucion'      => '2020-07-27',
            '--RUTEmisor'       => '78465260-2',
            '--RznSoc'          => 'INVERSIONES ANTUMALAL LIMITADA',
            '--GiroEmis'        => 'AGRICOLA',
            '--Acteco'          =>  726000,
            '--DirOrigen'       => 'FUNDO POTRERILLOS S N',
            '--CmnaOrigen'      => 'Monte Patria',
            '--RUTRecep'        => '81515100-3',
            '--RznSocRecep'     => 'SELIM DABED SPA.',
            '--GiroRecep'       => 'BARRACA Y FERRETERIA',
            '--DirRecep'        => 'BENAVENTE 516',
            '--CmnaRecep'       => 'OVALLE',
            '--RutEnvia'        => '15751871-2',
            '--RutReceptor'     => '60803000-K',
        ])->assertExitCode(0);

        $this->assertTrue(File::exists($file));
    }
}