<?php
/**
 * @version 28/10/20 1:50 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Sii\Certification;


use sasco\LibreDTE\Sii\Certificacion\SetPruebas;

class FileParser
{
    /**
     * @version 28/10/20
     * @author  David Lopez <dlopez@hsd.cl>
     */
    public function __construct()
    {

    }

    public static function parse(string $file, array $folios = [], $delimiter = '==============')
    {
        $documents = [];
    }
}
