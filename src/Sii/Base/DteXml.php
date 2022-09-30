<?php
/**
 * @version 29/9/22 5:20 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Sii\Base;

use sasco\LibreDTE\XML;

/**
 * Class DteXml
 * Clase que representa un DTE cargado desde un XML. Agrega las funciones de parseo y conversión.
 * @package HSDCL\DteCl\Sii\Base
 * @author  David Lopez <dleo.lopez@gmail.com>
 */
class DteXml extends XML implements Dte
{
    protected $xml;

    /**
     * @param string $version
     * @param string $encoding
     */
    public function __construct(string $xml, $version = '1.0', $encoding = 'ISO-8859-1')
    {
        $this->xml = $xml;
        parent::__construct($version, $encoding);
    }

    /**
     * @return DteStructure
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function convertToArray(): array
    {
        $this->loadXML($this->xml);

        return $this->toArray($this->documentElement);
    }
}
