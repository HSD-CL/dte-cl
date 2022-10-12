<?php
/**
 * @version 5/2/21 3:37 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Sii\Base\Pdf;

use HSDCL\DteCl\Sii\Base\Dte;

/**
 * Class PdfDte
 *
 * Lo ideal sería que el PDF se creara a partir de un XML
 *
 * @package HSDCL\DteCl\Sii\Base
 * @author  David Lopez <dleo.lopez@gmail.com>
 */
class PdfDte extends \sasco\LibreDTE\Sii\Dte\PDF\Dte
{
    /**
     * @param \SimpleXMLElement $xml
     * @param array $options
     * @version 5/2/21
     * @author  David Lopez <dlopez@hsd.cl>
     */
    public function __construct(array $options = [])
    {
        parent::__construct(false);
        $this->SetTitle('HSD');
        $this->SetAuthor('HSD - https://www.hsd.cl');
        $this->SetCreator('HSD - https://www.hsd.cl');
        $this->setFooterText('HSD - https://www.hsd.cl');
    }
}
