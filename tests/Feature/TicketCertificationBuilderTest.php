<?php

namespace HSDCL\DteCl\Tests\Feature;

use HSDCL\DteCl\Sii\Base\Dte;
use HSDCL\DteCl\Sii\Base\JsonSource;
use HSDCL\DteCl\Sii\Certification\FileSource;
use HSDCL\DteCl\Sii\Certification\TicketCertificationBuilder;
use HSDCL\DteCl\Util\Configuration;
use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Sii\Folios;

/**
 * Class TicketCertificationBuilderTest
 *
 * Prueba unitarias ticket certification
 * @package Feature
 * @author  David Lopez <dleo.lopez@gmail.com>
 */
class TicketCertificationBuilderTest extends \Orchestra\Testbench\TestCase
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
        $this->config = Configuration::getInstance('folios-', __DIR__ . '/../../resources/assets/xml/folios/39.xml');
        $this->firma = new FirmaElectronica(['file' => __DIR__ . '/../../resources/assets/certs/cert.pfx',
                                             'pass' => env('FIRMA_PASS')]);
        $this->folios = [
            Dte::BOLETA_ELECTRONICA => new Folios(file_get_contents(Configuration::getInstance('folios-' . Dte::FACTURA_ELECTRONICA, __DIR__ . '/../../resources/assets/xml/folios/39.xml')->getFilename())),
        ];
        $setPruebas = [
            // CASO 4
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 39,
                    ],
                ],
                'Detalle'    => [
                    [
                        'NmbItem' => 'item afecto 1',
                        'QtyItem' => 8,
                        'PrcItem' => 1590,
                    ],
                    [
                        'IndExe'  => 1,
                        'NmbItem' => 'item exento 2',
                        'QtyItem' => 2,
                        'PrcItem' => 1000,
                    ]
                ],
                'Referencia' => [
                    [
                        'CodRef'   => 'SET',
                        'RazonRef' => 'CASO-4',
                    ]
                ],
            ],
        ];
        $this->certification = new TicketCertificationBuilder(
            $this->firma,
            new JsonSource(json_encode($setPruebas)),
            $this->folios,
            $emisor,
            $receptor
        );
    }

    /**
     * @version 202207201230
     * @test
     * @author  David Lopez <dlopez@hsd.cl>
     */
    public function canInstance(): TicketCertificationBuilder
    {
        $this->assertInstanceOf(TicketCertificationBuilder::class, $this->certification);

        return $this->certification;
    }

    /**
     * @author  David Lopez <dleo.lopez@gmail.com>
     * @depends canInstance
     * @test
     **/
    public function canParse(TicketCertificationBuilder $certification)
    {
        $starFolio = [
            Dte::BOLETA_ELECTRONICA => '1'
        ];

        $this->assertInstanceOf(TicketCertificationBuilder::class, $certification->parse($starFolio));

        return $certification;
    }

    /**
     * @author  David Lopez <dleo.lopez@gmail.com>
     * @depends canParse
     * @test
     **/
    public function canStampAndSign(TicketCertificationBuilder $certification)
    {
        $startFolio = [
            Dte::BOLETA_ELECTRONICA => '1'
        ];
        $this->assertInstanceOf(TicketCertificationBuilder::class, $certification->setStampAndSign($startFolio));

        return $certification;
    }

    /**
     * @depends canInstance
     * @author  David Lopez <dleo.lopez@gmail.com>
     * @test
     **/
    public function canBuild(TicketCertificationBuilder $certification)
    {
        $startFolio = [
            Dte::BOLETA_ELECTRONICA => '6'
        ];
        $caratula = [
            'RutEnvia'    => env('RutEnvia'),
            'RutReceptor' => env('RutReceptor'),
            'FchResol'    => env('FechaResolucion'),
            'NroResol'    => 0,
        ];
        $this->assertInstanceOf(
            TicketCertificationBuilder::class,
            $certification->build($startFolio, $caratula)
        );

        return $certification;
    }

    /**
     * @depends canBuild
     * @author  David Lopez <dleo.lopez@gmail.com>
     * @test
     **/
    public function canMakeFile(TicketCertificationBuilder $certification)
    {
        $fileName = "/tmp/test.xml";
        $certification->export($fileName);
        $this->assertFileExists($fileName, "File doesn't exist");
    }

    /**
     * @depends canBuild
     * @author  David Lopez <dleo.lopez@gmail.com>
     * @test
     **/
    public function canBuildRcof(TicketCertificationBuilder $certification)
    {
        $fileName = "/tmp/test.xml";
        $response = $certification->buildRcof($fileName);
        $this->assertIsArray($response);
        var_dump($response);
    }
}