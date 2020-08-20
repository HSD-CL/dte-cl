<?php

namespace HSDCL\DteCl\Tests;

use HSDCL\DteCl\DteCl;
use HSDCL\DteCl\Sii\Certification\CertificationBuilder;
use HSDCL\DteCl\Sii\Certification\FileSource;
use HSDCL\DteCl\Sii\Certification\BasicCertificationBuilder;
use HSDCL\DteCl\Util\Configuration;
use Orchestra\Testbench\TestCase;
use HSDCL\DteCl\DteClServiceProvider;
use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Sii\Folios;

/**
 * Class ExampleTest
 * @package HSDCL\DteCl\Tests
 * @author David Lopez <dleo.lopez@gmail.com>
 */
class SaleCertificationTest extends TestCase
{
    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var FirmaElectronica
     */
    protected $firma;

    /**
     * @var Folios
     */
    protected $folios;

    /**
     * @var BasicCertificationBuilder
     */
    protected $certification;

    /**
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->config = Configuration::getInstance('folios-33', __DIR__ . '/../resources/assets/xml/folios/33.xml');
        # TODO Ocultar en un archivo de configuracion
        $this->firma = new FirmaElectronica(['file' => __DIR__ . '/../resources/assets/certs/cert.pfx', 'pass' => env('FIRMA_PASS')]);
        $this->folios = new Folios(file_get_contents($this->config->getFilename()));
        $this->certification = new BasicCertificationBuilder($this->firma, $this->folios, new FileSource(__DIR__ . '/../resources/assets/set_pruebas/001-basico.txt'));
    }

    /**
     * @test
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function canInstance()
    {
        $this->assertInstanceOf(BasicCertificationBuilder::class, $this->certification);
    }

    /**
     * @test
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function canParse()
    {
        $this->assertTrue($this->certification->parse());
    }

    /**
     * @test
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function canStampAndSign()
    {
        $this->assertTrue($this->certification->parse());
        $this->assertTrue($this->certification->setStampAndSign(56));
    }

    /**
     * @test
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function canBuild()
    {
        $caratula = [
            'RutEnvia'    => '11222333-4',
            'RutReceptor' => '60803000-K',
            'FchResol'    => '2014-12-05',
            'NroResol'    => 0,
        ];
        $this->assertInstanceOf(CertificationBuilder::class, $this->certification->build(56, $caratula));
    }
}
