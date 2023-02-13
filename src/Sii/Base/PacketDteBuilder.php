<?php
namespace HSDCL\DteCl\Sii\Base;

use HSDCL\DteCl\Sii\Base\Pdf\PdfDte;
use HSDCL\DteCl\Util\Exception;
use HSDCL\DteCl\Util\SignatureFactory;
use sasco\LibreDTE\File;
use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Sii;
use sasco\LibreDTE\Sii\Dte\PDF\Dte;
use sasco\LibreDTE\Sii\EnvioDte;
use sasco\LibreDTE\Sii\Folios;

/**
 * Builder para el envio de un DTE
 * @author David Lopez <dleo.lopez@gmail.com>
 * @version 5/8/20 6:32 p. m.
 */

abstract class PacketDteBuilder
{
    use SignatureFactory;

    /**
     * @var array
     */
    protected $parsed;

    /**
     * @var FirmaElectronica
     */
    protected $firma;

    /**
     * @var Folios
     */
    protected $folios;

    /**
     * @var EnvioDte
     */
    protected $agent;

    /**
     * @var Source
     */
    protected $source;

    /**
     * @var array
     */
    protected $issuing;

    /**
     * @var array
     */
    protected $receiver;

    /**
     * @param FirmaElectronica $firma
     * @param array $folios
     * @param Source $source
     * @param array $issuing
     * @param array $receiver
     */
    public function __construct(FirmaElectronica $firma, Source $source, array $folios = null, array $issuing = null,
                                array            $receiver = null, int $environment = Sii::CERTIFICACION)
    {
        $this->firma = $firma;
        $this->folios = $folios;
        $this->source = $source;
        $this->issuing = $issuing;
        $this->receiver = $receiver;
        # Definicion para enviar los archivos
        // trabajar en ambiente de certificación
        Sii::setAmbiente($environment);
    }

    /**
     * Funcion que convierte desde un origen los datos a un array de documentos
     * para enviar
     * @param \HSDCL\DteCl\Sii\Certification\Source $source
     * @return PacketDteBuilder
     */
    abstract public function parse(array $startFolios): PacketDteBuilder;

    /**
     * Enviara los documentos a la certificacion
     * @param array $caratula
     * @return mixed
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    abstract public function send();

    /**
     * @param array|null $startFolio
     * @return PacketDteBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    abstract public function setStampAndSign(array $startFolio = null): PacketDteBuilder;

    /**
     * @param array $caratula
     * @return PacketDteBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    abstract public function setCaratula(array $caratula): PacketDteBuilder;

    /**
     * @return PacketDteBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    abstract public function setSign(): PacketDteBuilder;

    /**
     * @param int|null $startFolio
     * @param array $caratula
     * @return bool
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    abstract public function build(array $startFolio, array $caratula): PacketDteBuilder;

    /**
     * @param string $filename
     * @return mixed
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    abstract public function export(string $filename);

    /**
     * @author David Lopez <dlopez@hsd.cl>
     * return EnvioDte
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @param EnvioDte $agent
     * @return PacketDteBuilder
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function setAgent(EnvioDte $agent)
    {
        $this->agent = $agent;

        return $this;
    }

    /**
     * @version 15/9/21
     * @author  David Lopez <dlopez@hsd.cl>
     */
    public function addFolios(int $type, FolioSource $source)
    {
        $this->folios[$type] = new Folios($source->getFolio());

        return $this->folios;
    }
}
