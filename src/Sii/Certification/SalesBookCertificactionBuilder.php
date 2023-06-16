<?php

/**
 * @author Danilo Vasquez
 * @version 24/8/20 3:55 p. m.
 */

namespace HSDCL\DteCl\Sii\Certification;

use HSDCL\DteCl\Sii\Base\PacketDteBuilder;
use HSDCL\DteCl\Sii\Base\Source;
use Illuminate\Support\Facades\Log;
use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Sii\EnvioDte;
use sasco\LibreDTE\Sii\LibroCompraVenta;

/**
 * Class SalesBookCertificactionBuilder
 * Clase para manejar la certificacion del libro de ventas
 * @package HSDCL\DteCl\Sii\Certification
 * @author Danilo Vasquez
 */
class SalesBookCertificactionBuilder extends PacketDteBuilder
{
    /**
     * @var array
     */
    protected $caratula;

    /**
     * @var bool
     */
    protected $simplify = false;

    /**
     * @var
     */
    protected $xml;

    /**
     * @var bool
     */
    protected $isDirty = true;

    /**
     * SalesBookCertificactionBuilder constructor.
     * @param FirmaElectronica $firma
     * @param Source $source
     * @param bool $simplify                    Check if book is normal or simplify
     * @param array|null $folios
     * @param array|null $issuing
     * @param array|null $receiver
     */
    public function __construct(FirmaElectronica $firma, Source $source, bool $simplify = false, array $folios = null, array $issuing = null, array $receiver = null)
    {
        parent::__construct($firma, $source, $folios, $issuing, $receiver);
        $this->agent = new LibroCompraVenta($simplify);
        $this->simplify = $simplify;
    }

    /**
     * @param array $startFolios
     * @return PacketDteBuilder
     * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
     */
    public function parse(array $startFolios = null): PacketDteBuilder
    {
        # We need read from xml
        $handler = new EnvioDte();
        $handler->loadXML(file_get_contents($this->source->getInput()));
        $documents = $handler->getDocumentos();
        foreach ($documents as $document) {
            $this->agent->agregar($document->getResumen(), false);
        }
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
     * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
     */
    public function setStampAndSign(array $startFolio = null): PacketDteBuilder
    {
        return $this;
    }

    /**
     * @param array $caratula
     * @return $this|PacketDteBuilder
     * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
     */
    public function setCaratula(array $caratula): PacketDteBuilder
    {
        # Se necesita definir para ser usada luego en el parse
        $this->caratula = $caratula;
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
        if (!$this->simplify) {
            $this->agent->setFirma($this->firma);
        }

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
        $this->parse()
            ->setCaratula($caratula)
            ->setSign()
        ;
        # Generar XML sin firma
        $this->xml = $this->agent->generar(!$this->simplify);
        if (!$this->agent->schemaValidate()) {
            $this->isDirty = true;
            Log::error("No se pudo generar el archivo para el libro de venta");
            return $this;
        }
        $this->isDirty = false;

        return $this;
    }

    /**
     * @param string $filename
     * @return mixed|void
     * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
     */
    public function export(string $filename)
    {
        if (!$this->isDirty) {
            $doc = new \DOMDocument();
            $doc->loadXML($this->xml);
            return $doc->save($filename);
        }

        return false;
    }
}
