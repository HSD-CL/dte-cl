<?php
/**
 * @version 2/8/23 6:52 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Sii\Base\Pdf;

use sasco\LibreDTE\Sii\Dte;

/**
 * Class FormatStrategy
 * Formulara la estrategia para imprimir el DTE
 * @package HSDCL\DteCl\Sii\Base\Pdf
 * @author  David Lopez <dlopez@arisa.cl>
 */
abstract class FormatStrategy
{
    /**
     * @var PdfDte
     */
    protected $pdf;

    /**
     * @var string|null
     */
    protected $logo;

    /**
     * @var array
     */
    protected $caratula;

    /**
     * @var bool
     */
    protected $cedible;

    /**
     * @var bool
     */
    protected $withoutTimbre;

    /**
     * @var string
     */
    protected $dirOuput;

    /**
     * @version 2/8/23
     * @author  David Lopez <dleo.lopez@gmail.com>
     */
    public function __construct(string $dirOutput, bool $thermalPaper = false, string $logo = null, array $caratula = [], bool $cedible = false, bool $withoutTimbre = false)
    {
        $this->pdf = new PdfDte($thermalPaper);
        $this->logo = $logo;
        $this->caratula = $caratula;
        $this->cedible = $cedible;
        $this->withoutTimbre = $withoutTimbre;
        $this->dirOuput = $dirOutput;
    }

    /**
     * Metodod que creará el PDF del DTE
     * @return mixed
     * @author David Lopez <dlopez@arisa.cl>
     */
    abstract public function build(Dte $dte, string $fileName);
}
