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
class BasicCertificationCommandTest extends TestCase
{
    /**
     * @test
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function canCallCommand()
    {
        $uuid = Str::uuid();
        // make sure we're starting from a clean state
        if (File::exists("/tmp/{$uuid}.xml")) {
            unlink("/tmp/{$uuid}.xml");
        }
        $this->assertFalse(File::exists("/tmp/{$uuid}.xml"));
        $this->artisan('dte:basic-certification', [
            '--folios-fe'   => '',
            '--folios-nc'   => '',
            '--folios-nd'   => '',
            '--firma'       => '',
            '--source'      => '',
            '--output'      => '',
            '--pass'        => '',
            '--start-fe'    => '',
            '--start-nd'    => '',
            '--start-nc'    => '',
            '--resolucion'  => '',
            '--RUTEmisor'   => '',
            '--RznSoc'      => '',
            '--GiroEmis'    => '',
            '--Acteco'      => '',
            '--DirOrigen'   => '',
            '--CmnaOrigen'  => '',
            '--RUTRecep'    => '',
            '--RznSocRecep' => '',
            '--GiroRecep'   => '',
            '--DirRecep'    => '',
            '--CmnaRecep'   => '',
            '--RutEnvia'    => '',
            '--RutReceptor' => '',
        ])->assertExitCode(0);

        $this->assertTrue(File::exists("/tmp/{$uuid}.xml"));
    }
}
