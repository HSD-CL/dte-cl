<?php

namespace HSDCL\DteCl\Tests\Feature;

use HSDCL\DteCl\Sii\Certification\CertificationBuilder;
use HSDCL\DteCl\Sii\Certification\FileSource;
use HSDCL\DteCl\Sii\Certification\BasicCertificationBuilder;
use HSDCL\DteCl\Sii\Certification\PurchaseBookCertificactionBuilder;
use HSDCL\DteCl\Util\Configuration;
use HSDCL\DteCl\Tests\TestCase;
use sasco\LibreDTE\FirmaElectronica;

/**
 * Class ExampleTest
 * @package HSDCL\DteCl\Tests
 * @author David Lopez <dleo.lopez@gmail.com>
 */
class PurchaseBookCertificationTest extends TestCase
{
    /**
     * @var FirmaElectronica
     */
    protected $firma;

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
        # TODO Ocultar en un archivo de configuracion
        $this->firma = new FirmaElectronica(['file' => __DIR__ . '/../resources/assets/certs/cert.pfx', 'pass' => env('PASS')]);
        $this->certification = new PurchaseBookCertificactionBuilder($this->firma, new FileSource(__DIR__ . '/../../resources/assets/set_pruebas/003-compras.txt'));
    }

    /**
     * @test
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function canInstance()
    {
        $this->assertInstanceOf(PurchaseBookCertificactionBuilder::class, $this->certification);
    }

    /**
     * @test
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function canParse()
    {
        $this->assertInstanceOf(PurchaseBookCertificactionBuilder::class, $this->certification->parse());
    }

    /**
     * @test
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function canStampAndSign()
    {
        $this->assertInstanceOf(PurchaseBookCertificactionBuilder::class, $this->certification->setStampAndSign());
    }

    /**
     * @test
     * @version
     * @author  David Lopez <dlopez@hsd.cl>
     */
    public function canSetCaratula()
    {
        $this->assertInstanceOf(PurchaseBookCertificactionBuilder::class, $this->certification->setCaratula([]));
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

    /**
     * @test
     * @author  David Lopez <dlopez@hsd.cl>
     */
    public function canExport()
    {
        unlink('/tmp/file.xml');
        // caratula del libro
        $caratula = [
            'RutEmisorLibro' => '76192083-9',
            'RutEnvia' => '11222333-4',
            'PeriodoTributario' => '2000-03',
            'FchResol' => '2006-01-20',
            'NroResol' => 102006,
            'TipoOperacion' => 'COMPRA',
            'TipoLibro' => 'ESPECIAL',
            'TipoEnvio' => 'TOTAL',
            'FolioNotificacion' => 102006,
        ];
        $this->certification->build([], $caratula);
        $this->assertIsInt($this->certification->export('/tmp/file.xml'));
        $this->assertFileExists('/tmp/file.xml');
        unlink('/tmp/file.xml');
    }
}
