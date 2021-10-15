<?php
/**
 * @author David Lopez <dleo.lopez@gmail.com>
 * @version 24/8/20 3:55 p. m.
 */

namespace HSDCL\DteCl\Sii\Certification;

use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Sii\LibroCompraVenta;

/**
 * Class PurchaseBookCertificactionBuilder
 * Clase para manejar la certificacion del libro de compras
 * @package HSDCL\DteCl\Sii\Certification
 * @author David Lopez <dleo.lopez@gmail.com>
 */
class PurchaseBookCertificactionBuilder extends CertificationBuilder
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
     * @return CertificationBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function parse(array $startFolios = null): CertificationBuilder
    {
        $this->agent->agregarComprasCSV($this->source->getInput());

        return $this;
    }

    /**
     * @return bool
     * @author David Lopez <dleo.lopez@gmail.com>
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
        $this->agent->setFirma($this->firma);

        return $this;
    }

    /**
     * @param array $caratula
     * @return $this|CertificationBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
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
     * @author David Lopez <dleo.lopez@gmail.com>
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
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function build(array $startFolio, array $caratula): CertificationBuilder
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
