<?php
/**
 * @version 2/8/23 6:55 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Sii\Base\Pdf;

use sasco\LibreDTE\Sii\Dte;

/**
 * Class LetterFormatStrategy
 * Genera un DTE en formato de papel carta
 * @package HSDCL\DteCl\Sii\Base\Pdf
 * @author  David Lopez <dlopez@arisa.cl>
 */
class LetterFormatStrategy extends FormatStrategy
{
    /**
     * @param string $dirOutput
     * @param string|null $logo
     * @param array $caratula
     * @param bool $cedible
     * @param bool $withoutTimbre
     */
    public function __construct(string $dirOutput, string $logo = null, array $caratula = [], bool $cedible = false, bool $withoutTimbre = false)
    {
        parent::__construct($dirOutput, false, $logo, $caratula, $cedible, $withoutTimbre);
    }

    /**
     * @return mixed|void
     * @author  David Lopez <dlopez@arisa.cl>
     */
    public function build(Dte $dte, string $fileName)
    {
        if (!empty($this->logo)) {
            $this->pdf->setLogo($this->logo);
        }
        if (!empty($this->caratula)) {
            $this->pdf->setResolucion([
                'FchResol' => $this->caratula['FchResol'] ?? '',
                'NroResol' => $this->caratula['NroResol'] ?? ''
            ]);
        }
        if ($this->cedible) {
            $this->pdf->setCedible($this->cedible);
        }
        $this->pdf->agregar($dte->getDatos(), $this->withoutTimbre ? null : $dte->getTED());
        $this->pdf->Output($this->dirOuput . '/dte_' . $fileName . '_' . $dte->getID(true) . '.pdf', 'F');
    }
}
