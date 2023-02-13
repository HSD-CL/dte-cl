<?php
/**
 * @version 13/2/23 10:25 a. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Sii\Base;

/**
 * Class XmlFolioSource
 * @package HSDCL\DteCl\Sii\Base
 * @author  David Lopez <dleo.lopez@gmail.com>
 */
class XmlFolioSource implements FolioSource
{
    protected $xml;

    /**
     * @param string $xml Folio
     */
    public function __construct(string $xml)
    {
        $this->xml = $xml;
    }

    /**
     * Obtiene el folio desde el XML
     * @return string
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function getFolio()
    {
        return $this->xml;
    }
}
