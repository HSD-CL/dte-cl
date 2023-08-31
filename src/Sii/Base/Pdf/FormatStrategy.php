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
     * @var string
     */
    protected $dirOuput;

    /**
     * @var array
     */
    protected $options;

    /**
     * Constructor
     *
     * Options puede terner
     * thermal_paper    Boolean         True Es papel continuo, False Tipo Carta
     * logo             String          Data de la imagen del logo
     * cedible          Boolean         True El documento es cedible, False No es cedible
     * without_timbre   Boolean         True El documento no lleva timbre, False Si lleva timbre fiscal
     * caratula         Array           Datos de la caratula
     * @version 2/8/23
     * @author David Lopez <dlopez@arisa.cl>>
     */
    public function __construct(string $dirOutput, array $options = [])
    {
        $this->dirOuput = $dirOutput;
        $this->options = $options;
    }

    /**
     * Metodod que creará el PDF del DTE
     * @return mixed
     * @author David Lopez <dlopez@arisa.cl>
     */
    abstract public function build(Dte $dte, string $fileName, array $extraInfo = []);
}
