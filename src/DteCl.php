<?php

namespace HSDCL\DteCl;

use sasco\LibreDTE\Sii\Autenticacion;

/**
 * Class DteCl
 * @package HSDCL\DteCl
 * @author David Lopez <dleo.lopez@gmail.com>
 */
class DteCl
{
    /**
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function getLogin()
    {
        $token = Autenticacion::getToken([
            'file' => __DIR__ . '/../resources/assets/cert.pfx',
            'pass' => 'Aaraneda1*'
        ]);

        return $token;
    }
}
