<?php
/**
 * @version 13/2/23 10:17 a. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Sii\Base;

use HSDCL\DteCl\Util\Configuration;

/**
 * Class FileFolioSource
 * @package HSDCL\DteCl\Sii\Base
 * @author  David Lopez <dleo.lopez@gmail.com>
 */
class FileFolioSource implements FolioSource
{
    /**
     * @var string PATH archivo que contiene el folio
     */
    protected $fileName;

    /**
     * @version 13/2/23
     * @author  David Lopez <dleo.lopez@gmail.com>
     */
    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * Obtiene la data del folio desde el archivo
     * @author David Lopez <dleo.lopez@gmail.com>
     * @return mixed
     */
    public function getFolio()
    {
        return file_get_contents($this->fileName);
    }
}
