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
class SaleCertificationCommandTest extends TestCase
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
        $this->artisan('dte:sale-certification', [
            '--folios-fe'  => base_path() . '/../../../../resources/assets/xml/folios/33.xml',
            '--folios-nc'  => base_path() . '/../../../../resources/assets/xml/folios/61.xml',
            '--folios-nd'  => base_path() . '/../../../../resources/assets/xml/folios/56.xml',
            '--firma'      => base_path() . '/../../../../resources/assets/certs/cert.pfx',
            '--source'     => base_path() . '/../../../../resources/assets/set_pruebas/001-basico.txt',
            '--output'     => "/tmp/{$uuid}.xml",
            '--pass'       => 'Aaraneda1*',
            '--start-fe'   => '80',
            '--start-nd'   => '56',
            '--start-nc'   => '63',
            '--resolucion' => '2020-07-27'
        ])->assertExitCode(0);

        $this->assertTrue(File::exists("/tmp/{$uuid}.xml"));
    }
}
