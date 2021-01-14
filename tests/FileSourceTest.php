<?php

namespace HSDCL\DteCl\Tests;

use HSDCL\DteCl\Sii\Certification\FileSource;

/**
 * Class ExampleTest
 * @package HSDCL\DteCl\Tests
 * @author David Lopez <dleo.lopez@gmail.com>
 */
class FileSourceTest extends TestCase
{
    /**
     * @test
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function canGetCases()
    {
        $source = new FileSource(__DIR__ . '/../resources/assets/set_pruebas/001-basico.txt');
        $this->assertIsArray($source->getCases());
        $this->assertNotEmpty($source->getCases());
    }
}
