<?php
/**
 * @version 14/1/21 1:57 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Sii\Certification;


use HSDCL\DteCl\Sii\Base\Source;
use sasco\LibreDTE\FirmaElectronica;

/**
 * Class ExemptCertificationBuilder
 * Funciones para la certificación del set de pruebas exento
 * @package HSDCL\DteCl\Sii\Certification
 * @author  David Lopez <dleo.lopez@gmail.com>
 */
class ExemptCertificationBuilder extends BasicCertificationBuilder
{
    /**
     * ExemptCertificationBuilder constructor.
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
