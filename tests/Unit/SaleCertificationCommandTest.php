<?php

namespace HSDCL\DteCl\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use HSDCL\DteCl\Tests\TestCase;

/**
 * Class ExampleTest
 * @package HSDCL\DteCl\Tests
 * @author David Lopez <dleo.lopez@gmail.com>
 */
class DteClTest extends TestCase
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
        dd(env('FIRMA_PASS'));
        $this->assertFalse(File::exists('/tmp/file.xml'));
        $this->artisan('dte:sale-certification', [
            '--folios-fe' => base_path() . '/../../../../resources/assets/xml/folios/33.xml',
            '--folios-nc' => base_path() . '/../../../../resources/assets/xml/folios/61.xml',
            '--folios-nd' => base_path() . '/../../../../resources/assets/xml/folios/56.xml',
            '--firma'     => base_path() . '/../../../../resources/assets/certs/cert.pfx',
            '--source'    => base_path() . '/../../../../resources/assets/set_pruebas/001-basico.txt',
            '--output'    => '/tmp/file.xml',
            '--pass'      => env('FIRMA_PASS'),
            '--start-fe'  => '1',
            '--start-nd'  => '1',
            '--start-nc'  => '1',
        ])->assertExitCode(0);

        $this->assertTrue(File::exists('/tmp/file.xml'));
    }
}
