<?php
/**
 * @version 7/12/21 8:57 a. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Util;

use sasco\LibreDTE\FirmaElectronica;

trait SignatureFactory
{
    /**
     * Factory Firma Electronica
     * @param array $config
     * @return FirmaElectronica
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public static function makeSignature(array $config)
    {
        # TODO Validation
        return new FirmaElectronica($config);
    }

    /**
     * Factory Firma Electronica a partir archivo y password
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public static function makeFirma(string $file, string $pass): FirmaElectronica
    {
        return self::makeSignature(['file' => $file, 'pass' => $pass]);
    }
}
