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
class OfficeGuideCertificactionCommandTest extends TestCase
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
        $this->artisan('dte:office-guide-certification', [
            '--firma'     => base_path() . '/../../../../resources/assets/certs/cert.pfx',
            '--source'    => base_path() . '/../../../../resources/assets/set_pruebas/005-guia_despacho.txt',
            '--output'    => $file,
            '--pass'      => 'Aaraneda1*'
        ])->assertExitCode(0);

        $this->assertTrue(File::exists($file));
    }
}


// revisar /Volumes/DEVS/WEB/libredte-lib/examples/009-dte_33.php