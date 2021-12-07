<?php

namespace HSDCL\DteCl\Tests\Feature;

use HSDCL\DteCl\Sii\Certification\PacketDteBuilder;
use HSDCL\DteCl\Sii\Certification\FileSource;
use HSDCL\DteCl\Sii\Certification\BasicCertificationBuilder;
use HSDCL\DteCl\Sii\Certification\PurchaseBookCertificactionBuilder;
use HSDCL\DteCl\Util\Configuration;
use HSDCL\DteCl\Tests\TestCase;
use Illuminate\Support\Str;
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
        $this->certification = new PurchaseBookCertificactionBuilder($this->firma, new FileSource(__DIR__ . '/../../resources/assets/set_pruebas/002-compras.csv'));
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
        $this->assertInstanceOf(PacketDteBuilder::class, $this->certification->build(56, $caratula));
    }

    /**
     * @test
     * @author  David Lopez <dlopez@hsd.cl>
     */
    public function canExport()
    {
        $uuid = Str::uuid();
        $file = "/tmp/{$uuid}.xml";
        if (file_exists($file)) {
            unlink($file);
        }
        // caratula del libro
        $caratula = [
            'RutEnvia'          => '',
            'FchResol'          => '2006-01-20',
            'NroResol'          => 102006,
            'TipoLibro'         => 'ESPECIAL',
            'TipoEnvio'         => 'TOTAL',
            'TipoOperacion'     => 'COMPRA',
            'RutEmisorLibro'    => '',
            'FolioNotificacion' => 102006,
            'PeriodoTributario' => '2000-03',
        ];
        $this->certification->build([], $caratula);
        $this->assertIsInt($this->certification->export($file));
        $this->assertFileExists($file);
    }
}
