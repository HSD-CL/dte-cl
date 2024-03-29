<?php
/**
 * @version 14/1/21 2:23 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Tests\Feature;


use HSDCL\DteCl\Sii\Certification\BasicCertificationBuilder;
use HSDCL\DteCl\Sii\Certification\ExportCertificactionBuilder;
use HSDCL\DteCl\Sii\Certification\FileSource;
use HSDCL\DteCl\Tests\TestCase;
use HSDCL\DteCl\Util\Configuration;
use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Sii\Folios;
use HSDCL\DteCl\Sii\Base\Dte;

class ExportCertificationBuilderTest extends TestCase
{
    /**
     * @var FirmaElectronica
     */
    protected $firma;

    /**
     * @var Folios
     */
    protected $folios;

    /**
     * @var ExportCertificactionBuilder
     */
    protected $certification;

    /**
     * @throws \HSDCL\DteCl\Util\Exception
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
        $dotenv->load();
        $emisor = [
            'RUTEmisor'  => env('RUTEmisor'),
            'RznSoc'     => env('RznSoc'),
            'GiroEmis'   => env('GiroEmis'),
            'Acteco'     => env('Acteco'),
            'DirOrigen'  => env('DirOrigen'),
            'CmnaOrigen' => env('CmnaOrigen'),
        ];

        $receptor = [
            'RUTRecep'    => env('RUTRecep'),    #'81515100-3',
            'RznSocRecep' => env('RznSocRecep'), #'SELIM DABED SPA.',
            'GiroRecep'   => env('GiroRecep'),   #'BARRACA Y FERRETERIA',
            'DirRecep'    => env('DirRecep'),    #'BENAVENTE 516',
            'CmnaRecep'   => env('CmnaRecep')    #'OVALLE',
        ];
        $this->config = Configuration::getInstance('folios-34', __DIR__ . '/../../resources/assets/xml/folios/110.xml');
        $this->firma = new FirmaElectronica(['file' => __DIR__ . '/../../resources/assets/certs/cert.pfx', 'pass' => env('FIRMA_PASS')]);
        $this->folios = [
            Dte::FACTURA_DE_EXPORTACION         => new Folios(file_get_contents(Configuration::getInstance('folios-' . Dte::FACTURA_DE_EXPORTACION, __DIR__ . '/../../resources/assets/xml/folios/110.xml')->getFilename())),
            Dte::NOTA_DE_CREDITO_DE_EXPORTACION => new Folios(file_get_contents(Configuration::getInstance('folios-' . Dte::NOTA_DE_CREDITO_DE_EXPORTACION, __DIR__ . '/../../resources/assets/xml/folios/112.xml')->getFilename())),
            Dte::NOTA_DE_DEBITO_DE_EXPORTACION  => new Folios(file_get_contents(Configuration::getInstance('folios-' . Dte::NOTA_DE_DEBITO_DE_EXPORTACION, __DIR__ . '/../../resources/assets/xml/folios/111.xml')->getFilename())),
        ];
        $this->certification = new ExportCertificactionBuilder(
            $this->firma,
            new FileSource(__DIR__ . '/../../resources/assets/set_pruebas/009-set_exportacion_1.txt'),
            $this->folios,
            $emisor,
            $receptor
        );
    }

    /**
     * @test
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function canInstance(): ExportCertificactionBuilder
    {
        $this->assertInstanceOf(ExportCertificactionBuilder::class, $this->certification);

        return $this->certification;
    }

    /**
     * @depends canInstance
     * @test
     * @param ExportCertificactionBuilder $certification
     * @author  David Lopez <dlopez@hsd.cl>
     */
    public function canParse(ExportCertificactionBuilder $certification): ExportCertificactionBuilder
    {
        $startFolios = [
            Dte::FACTURA_DE_EXPORTACION         => '1',
            Dte::NOTA_DE_CREDITO_DE_EXPORTACION => '1',
            Dte::NOTA_DE_CREDITO_DE_EXPORTACION => '1',
        ];
        $this->assertInstanceOf(ExportCertificactionBuilder::class, $certification->parse($startFolios));

        return $certification;
    }

    /**
     * @test
     * @depends canParse
     * @param ExportCertificactionBuilder $certification
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function canStampAndSign(ExportCertificactionBuilder $certification): ExportCertificactionBuilder
    {
        $this->assertInstanceOf(ExportCertificactionBuilder::class, $certification->setStampAndSign());

        return $certification;
    }

    /**
     * @test
     * @depends canStampAndSign
     * @param ExportCertificactionBuilder $certification
     * @version 18/1/21
     * @author  David Lopez <dlopez@hsd.cl>
     */
    public function canExport(ExportCertificactionBuilder $certification)
    {
        $output = 'file.xml';
        $this->assertGreaterThan(0, $certification->export($output));
    }

    /**
     * @test
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function canExportToPdfSetOne()
    {
        $this->assertTrue(
            BasicCertificationBuilder::exportToPdf(
                '/home/dlopez/Projects/Php/dte-cl/resources/assets/xml/exportacion_1/1.xml',
                __DIR__ . '/../../resources/assets/img/logo.png',
                __DIR__ . '/../../pdf/4_exportacion_1/'
            ))
        ;
    }

    /**
     * @test
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function canExportToPdfSetTwo()
    {
        $this->assertTrue(
            BasicCertificationBuilder::exportToPdf(
                '/home/dlopez/Projects/Php/dte-cl/resources/assets/xml/exportacion_2/1.xml',
                __DIR__ . '/../../resources/assets/img/logo.png',
                __DIR__ . '/../../pdf/5_exportacion_2/'
            ))
        ;
    }
}
