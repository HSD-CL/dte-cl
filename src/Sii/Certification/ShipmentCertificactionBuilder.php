<?php

/**
 * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
 * @version 24/8/20 3:55 p. m.
 */

namespace HSDCL\DteCl\Sii\Certification;

use HSDCL\DteCl\Util\Exception;
use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Sii\Dte;
use sasco\LibreDTE\Sii\EnvioDte;
use \sasco\LibreDTE\Sii\Folios;
use \sasco\LibreDTE\Sii\Certificacion\SetPruebas;

/**
 * Class ShipmentCertificactionBuilder
 * Clase para manejar la certificacion de guia de despacho
 * @package HSDCL\DteCl\Sii\Certification
 * @author Danilo Vasques <dvasquezr.ko@gmail.com>
 */
class ShipmentCertificactionBuilder extends CertificationBuilder
{
    /**
     * @var array
     */
    protected $caratula;
    protected $DTE;

    public $data;

    /**
     * ShipmentCertificactionBuilder constructor.
     * @param FirmaElectronica $firma
     * @param array $folios
     * @param Source $source
     * @param array $issuing
     * @param array $receiver
     */
    public function __construct(FirmaElectronica $firma, Source $source, array $folios = null, array $issuing = null, array $receiver = null)
    {
        parent::__construct($firma, $source, $folios, $issuing, $receiver);

        $this->agent = new EnvioDte();
    }

    /**
     * @param array $startFolios
     * @return CertificationBuilder
     * @author Danilo Vasquez
     */
    public function parse(array $startFolios = null): CertificationBuilder
    {
        $this->data = $this->source->getCases($startFolios);
        return $this;
    }

    /**
     * @return bool
     * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
     */
    public function send(): bool
    {
        $this->agent->enviar();

        return true;
    }

    /**
     * @param array|null $startFolio
     * @return CertificationBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function setStampAndSign(array $startFolio = null): CertificationBuilder
    {
        // generar cada DTE, timbrar, firmar y agregar al sobre de EnvioDTE
        foreach ($this->data as $document) {
            # Agregar emisor
            $document['Encabezado']['Emisor'] = $this->issuing;
            # Agregar el receptor
            $document['Encabezado']['Receptor'] = $this->receiver;

            $dte = new Dte($document);
            if (!$dte->timbrar($this->folios[52])) {
                throw new Exception('No se pudo timbrar el dte');
            }
            if (!$dte->firmar($this->firma)) {
                throw new Exception('No se pudo firmar el dte');
            }
            if (!$this->agent->agregar($dte)) {
                throw new Exception('No se pudo agregar el dte');
            };
        }
        return $this;
    }

    /**
     * @param array $caratula
     * @return $this|CertificationBuilder
     * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
     */
    public function setCaratula(array $caratula): CertificationBuilder
    {
        # Agregar caratula por el agente
        $this->agent->setCaratula($caratula);

        return $this;
    }

    /**
     * @return CertificationBuilder
     * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
     */
    public function setSign(): CertificationBuilder
    {
        $this->agent->setFirma($this->firma);

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
        $this->parse($startFolio)
        ->setStampAndSign($startFolio)
        ->setCaratula($caratula)
        ->setSign();

        return $this;
    }

    /**
     * @param string $filename
     * @return mixed|void
     * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
     */
    public function export(string $filename)
    {
        $this->agent->generar();

        $doc = new \DOMDocument();
        $doc->loadXML($this->agent->saveXML());

        return $doc->save($filename);
    }
}
