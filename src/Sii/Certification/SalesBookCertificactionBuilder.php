<?php

/**
 * @author Danilo Vasquez
 * @version 24/8/20 3:55 p. m.
 */

namespace HSDCL\DteCl\Sii\Certification;

use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Sii\LibroCompraVenta;

/**
 * Class SalesBookCertificactionBuilder
 * Clase para manejar la certificacion del libro de ventas
 * @package HSDCL\DteCl\Sii\Certification
 * @author Danilo Vasquez
 */
class SalesBookCertificactionBuilder extends CertificationBuilder
{
    /**
     * @var array
     */
    protected $caratula;

    /**
     * SalesBookCertificactionBuilder constructor.
     * @param FirmaElectronica $firma
     * @param array $folios
     * @param Source $source
     * @param array $issuing
     * @param array $receiver
     */
    public function __construct(FirmaElectronica $firma, Source $source, array $folios = null, array $issuing = null, array $receiver = null)
    {
        parent::__construct($firma, $source, $folios, $issuing, $receiver);
        $this->agent = new LibroCompraVenta(true);
    }

    /**
     * @param array $startFolios
     * @return CertificationBuilder
     * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
     */
    public function parse(array $startFolios = null): CertificationBuilder
    {
        $this->agent->agregarVentasCSV($this->source->getInput());
        
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
     * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
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
        $this->parse()
            ->setCaratula($caratula);
        # Generar XML sin firma
        $this->agent->generar();
        $this->setStampAndSign();

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
        $doc->loadXML($this->agent->saveXML());

        return $doc->save($filename);
    }
}
