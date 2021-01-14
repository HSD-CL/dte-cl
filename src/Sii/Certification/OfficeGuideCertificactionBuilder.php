<?php
/**
 * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
 * @version 24/8/20 3:55 p. m.
 */

namespace HSDCL\DteCl\Sii\Certification;

use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Sii\Dte;
use sasco\LibreDTE\Sii\EnvioDte;

/**
 * Class OfficeGuideCertificactionBuilder
 * Clase para manejar la certificacion de guia de despacho
 * @package HSDCL\DteCl\Sii\Certification
 * @author Danilo Vasques <dvasquezr.ko@gmail.com>
 */
class OfficeGuideCertificactionBuilder extends CertificationBuilder
{
    /**
     * @var array
     */
    protected $caratula;
    protected $DTE;
    protected $EnvioDTE;
    
    public $data;

    /**
     * OfficeGuideCertificactionBuilder constructor.
     * @param FirmaElectronica $firma
     * @param array $folios
     * @param Source $source
     * @param array $issuing
     * @param array $receiver
     */
    public function __construct(FirmaElectronica $firma, Source $source, array $folios = null, array $issuing = null, array $receiver = null)
    {
        parent::__construct($firma, $source, $folios, $issuing, $receiver);

        $this->data = [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => 52,
                    'Folio' => 1,
                    'IndTraslado' => 5,
                    'TipoDespacho' => 1
                ],
                'Emisor' => [
                    'RUTEmisor' => '76192083-9',
                    'RznSoc' => 'SASCO SpA',
                    'GiroEmis' => 'Servicios integrales de informática',
                    'Acteco' => 726000,
                    'DirOrigen' => 'Santiago',
                    'CmnaOrigen' => 'Santiago',
                ],
                'Receptor' => [
                    'RUTRecep' => '60803000-K',
                    'RznSocRecep' => 'Servicio de Impuestos Internos',
                    'GiroRecep' => 'Gobierno',
                    'DirRecep' => 'Alonso Ovalle 680',
                    'CmnaRecep' => 'Santiago',
                ],
            ],
            'Detalle' => [
                [
                    'NmbItem' => 'Cajón AFECTO',
                    'QtyItem' => 123,
                    'PrcItem' => 923,
                ],
                [
                    'NmbItem' => 'Relleno AFECTO',
                    'QtyItem' => 53,
                    'PrcItem' => 1473,
                ],
            ],
        ];

        $this->DTE = new Dte($this->data);
        $this->EnvioDTE = new EnvioDte();
    }

    /**
     * @param array $startFolios
     * @return CertificationBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function parse(array $startFolios = null): CertificationBuilder
    {
        return $this;
    }

    /**
     * @return bool
     * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
     */
    public function send(): bool
    {
        $this->EnvioDTE->enviar();

        return true;
    }

    /**
     * @param array|null $startFolio
     * @return CertificationBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function setStampAndSign(array $startFolio = null): CertificationBuilder
    {
        $this->agent->setFirma($this->firma);

        return $this;
    }

    /**
     * @param array $caratula
     * @return $this|CertificationBuilder
     * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
     */
    public function setCaratula(array $caratula): CertificationBuilder
    {
        # Se necesita definir para ser usada luego en el parse
        $this->caratula = $caratula;
        # Agregar caratula por el agente
        $this->EnvioDTE->setCaratula($caratula);

        return $this;
    }

    /**
     * @return CertificationBuilder
     * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
     */
    public function setSign(): CertificationBuilder
    {
        $this->EnvioDTE->setFirma($this->firma);

        return $this;
    }

    /**
     * @param array $startFolio
     * @param array $caratula
     * @return CertificationBuilder
     * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
     */
    public function build(array $startFolio, array $caratula): CertificationBuilder
    {
       // $this->DTE->timbrar($startFolio);
        $this->DTE->firmar($this->firma);
        # Generar XML sin firma
        $this->EnvioDTE->agregar($this->DTE);
        $this->EnvioDTE->setFirma($this->firma);
        $this->EnvioDTE->setCaratula($caratula);
        $this->EnvioDTE->generar();

        return $this;
    }

    /**
     * @param string $filename
     * @return mixed|void
     * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
     */
    public function export(string $filename)
    {
        $doc = new \DOMDocument();
        $doc->loadXML($this->EnvioDTE->saveXML());

        return $doc->save($filename);
    }
}
