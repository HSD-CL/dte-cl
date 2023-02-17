<?php

/**
 * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
 * @version 24/8/20 3:55 p. m.
 */

namespace HSDCL\DteCl\Sii\Certification;

use HSDCL\DteCl\Sii\Base\PacketDteBuilder;
use HSDCL\DteCl\Sii\Base\Source;
use HSDCL\DteCl\Util\Exception;
use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Sii\Dte;
use sasco\LibreDTE\Sii\EnvioDte;

/**
 * Class ShipmentCertificationBuilder
 * Clase para manejar la certificacion de guia de despacho
 * @package HSDCL\DteCl\Sii\Certification
 * @author Danilo Vasques <dvasquezr.ko@gmail.com>
 */
class ShipmentCertificationBuilder extends PacketDteBuilder
{
    /**
     * @var array
     */
    protected $caratula;
    protected $DTE;

    public $data;

    /**
     * ShipmentCertificationBuilder constructor.
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
     * @return PacketDteBuilder
     * @author Danilo Vasquez
     */
    public function parse(array $startFolios = null): PacketDteBuilder
    {
        $this->data = $this->source->getCases($startFolios);
        return $this;
    }

    /**
     * @return bool
     * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
     */
    public function send()
    {
        return $this->agent->enviar();
    }

    /**
     * @param array|null $startFolio
     * @return PacketDteBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function setStampAndSign(array $startFolio = null): PacketDteBuilder
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
     * @return $this|PacketDteBuilder
     * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
     */
    public function setCaratula(array $caratula): PacketDteBuilder
    {
        # Agregar caratula por el agente
        $this->agent->setCaratula($caratula);

        return $this;
    }

    /**
     * @return PacketDteBuilder
     * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
     */
    public function setSign(): PacketDteBuilder
    {
        $this->agent->setFirma($this->firma);

        return $this;
    }

    /**
     * @param array $startFolio
     * @param array $caratula
     * @return PacketDteBuilder
     * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
     */
    public function build(array $startFolio, array $caratula): PacketDteBuilder
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
