<?php
/**
 * @version 9/12/21 8:48 a. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Sii\Base;

use sasco\LibreDTE\Sii;

/**
 * Class Environment
 * @package HSDCL\DteCl\Sii\Base
 * @author  David Lopez <dleo.lopez@gmail.com>
 */
final class Environment
{
    /**
     * Ambiente Producción
     */
    const PRODUCTION = Sii::PRODUCCION;

    /**
     * Ambiente certificacion
     */
    const CERTIFICATION = Sii::CERTIFICACION;

    /**
     * No se permitir instanciar
     * @author David López <dlopez@hsd.cl>
     */
    private function __construct() {}

    /**
     * @param int $environment
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public static function set(int $environment)
    {
        Sii::setAmbiente($environment);
    }
}
