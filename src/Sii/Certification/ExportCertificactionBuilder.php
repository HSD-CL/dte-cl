<?php

/**
 * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
 * @version 24/8/20 3:55 p. m.
 */

namespace HSDCL\DteCl\Sii\Certification;

use HSDCL\DteCl\Sii\Base\Source;
use HSDCL\DteCl\Util\Exception;
use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Sii\Dte;
use sasco\LibreDTE\Sii\EnvioDte;
use \sasco\LibreDTE\Sii\Folios;
use \sasco\LibreDTE\Sii\Certificacion\SetPruebas;

/**
 * Class ExportCertificactionBuilder
 * Clase para manejar la certificacion de guia de despacho
 * @package HSDCL\DteCl\Sii\Certification
 * @author Danilo Vasques <dvasquezr.ko@gmail.com>
 * @author David Lopez <dleo.lopez@gmail.com>
 */
class ExportCertificactionBuilder extends BasicCertificationBuilder
{
    /**
     * ExportCertificationBuilder constructor.
     * @param FirmaElectronica $firma
     * @param Source $source
     * @param array|null $folios
     * @param array|null $issuing
     * @param array|null $receiver
     */
    public function __construct(FirmaElectronica $firma, Source $source, array $folios = null, array $issuing = null, array $receiver = null)
    {
        parent::__construct($firma, $source, $folios, $issuing, $receiver);
    }
}
