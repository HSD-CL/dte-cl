<?php
namespace HSDCL\DteCl\Sii\Certification;

use HSDCL\DteCl\Util\Exception;
use sasco\LibreDTE\File;
use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Sii\Dte\PDF\Dte;
use sasco\LibreDTE\Sii\EnvioDte;
use sasco\LibreDTE\Sii\Folios;

/**
 * @author David Lopez <dleo.lopez@gmail.com>
 * @version 5/8/20 6:32 p. m.
 */

abstract class CertificationBuilder
{
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
    public function __construct(FirmaElectronica $firma, Source $source, array $folios = null, array $issuing = null, array $receiver = null)
    {
        $this->firma = $firma;
        $this->folios = $folios;
        $this->source = $source;
        $this->issuing = $issuing;
        $this->receiver = $receiver;
    }

    /**
     * Funcion que convierte desde un origen los datos a un array de documentos
     * para enviar
     * @param \HSDCL\DteCl\Sii\Certification\Source $source
     * @return CertificationBuilder
     */
    abstract public function parse(array $startFolios): CertificationBuilder;

    /**
     * Enviara los documentos a la certificacion
     * @param array $caratula
     * @return bool
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    abstract public function send(): bool;

    /**
     * @param array|null $startFolio
     * @return CertificationBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    abstract public function setStampAndSign(array $startFolio = null): CertificationBuilder;

    /**
     * @param array $caratula
     * @return CertificationBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    abstract public function setCaratula(array $caratula): CertificationBuilder;

    /**
     * @return CertificationBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    abstract public function setSign(): CertificationBuilder;

    /**
     * @param int|null $startFolio
     * @param array $caratula
     * @return bool
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    abstract public function build(array $startFolio, array $caratula): CertificationBuilder;

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
     * @return CertificationBuilder
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function setAgent(EnvioDte $agent)
    {
        $this->agent = $agent;

        return $this;
    }
}
