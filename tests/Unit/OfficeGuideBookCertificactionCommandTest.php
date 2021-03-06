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
class OfficeGuideBookCertificactionCommandTest extends TestCase
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
        $this->artisan('dte:office-guide-book-certification', [
            '--firma'               => base_path() . '/../../../../resources/assets/certs/danilo.p12',
            '--source'              => base_path() . '/../../../../resources/assets/set_pruebas/libro_guias.csv',
            '--output'              => $file,
            '--pass'                => 'Antumalal1*',
            '--RutEmisorLibro'      => '78465260-2',
            '--FchResol'            => '2020-07-27',
            '--NroResol'            => 0,
            '--FolioNotificacion'   => 102006
        ])->assertExitCode(0);

        $this->assertTrue(File::exists($file));
    }
}