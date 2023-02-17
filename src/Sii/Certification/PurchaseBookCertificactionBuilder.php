<?php
/**
 * @author David Lopez <dleo.lopez@gmail.com>
 * @version 24/8/20 3:55 p. m.
 */

namespace HSDCL\DteCl\Sii\Certification;

use HSDCL\DteCl\Sii\Base\PacketDteBuilder;
use HSDCL\DteCl\Sii\Base\Source;
use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Sii\LibroCompraVenta;

/**
 * Class PurchaseBookCertificactionBuilder
 * Clase para manejar la certificacion del libro de compras
 * @package HSDCL\DteCl\Sii\Certification
 * @author David Lopez <dleo.lopez@gmail.com>
 */
class PurchaseBookCertificactionBuilder extends PacketDteBuilder
{
    /**
     * @var array
     */
    protected $caratula;

    /**
     * @var
     */
    protected $xml;

    /**
     * @var bool
     */
    protected $isDirty = true;

    /**
     * PurchaseBookCertificactionBuilder constructor.
     * @param FirmaElectronica $firma
     * @param array $folios
     * @param Source $source
     * @param array $issuing
     * @param array $receiver
     */
    public function __construct(FirmaElectronica $firma, Source $source, array $folios = null, array $issuing = null, array $receiver = null)
    {
        parent::__construct($firma, $source, $folios, $issuing, $receiver);
        $this->agent = new LibroCompraVenta(false);
    }

    /**
     * @param array $startFolios
     * @return PacketDteBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function parse(array $startFolios = null): PacketDteBuilder
    {
        $this->agent->agregarComprasCSV($this->source->getInput());

        return $this;
    }

    /**
     * @return bool
     * @author David Lopez <dleo.lopez@gmail.com>
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
        $this->agent->setFirma($this->firma);

        return $this;
    }

    /**
     * @param array $caratula
     * @return $this|PacketDteBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
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
     * @author David Lopez <dleo.lopez@gmail.com>
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
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function build(array $startFolio, array $caratula): PacketDteBuilder
    {
        $this->setCaratula($caratula)
            ->parse()
            ->setStampAndSign()
        ;
        # Generar XML sin firma
        $this->xml = $this->agent->generar();
        if ($this->agent->schemaValidate()) {
            $this->isDirty = false;
            return $this;
        }
        $this->isDirty = true;

        return $this;
    }

    /**
     * @param string $filename
     * @return mixed|void
     * @author David Lopez <dleo.lopez@gmail.com>
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
