<?php

namespace HSDCL\DteCl\Tests\Unit;
use HSDCL\DteCl\Tests\TestCase;

/**
 * Class OfficeGuideExportPdfCommandTest
 * @package HSDCL\DteCl\Tests
 * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
 */
class OfficeGuideExportPdfCommandTest extends TestCase
{
    /**
     * @test
     * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
     */
    public function canCallCommand()
    {
        $this->artisan('dte:office-guide-export-pdf', [
            '--source'    => base_path() . '/../../../../xml/guia.xml',
            '--logo'      => base_path() . '/../../../../resources/assets/img/logo.png',
            '--output'    => base_path() . '/../../../../pdf',
        ])->assertExitCode(0);
    }
}
