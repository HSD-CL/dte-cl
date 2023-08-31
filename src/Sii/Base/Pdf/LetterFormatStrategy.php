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
     * @param array $options
     */
    public function __construct(string $dirOutput, array $options)
    {
        $options['thermal_paper'] = false;
        parent::__construct($dirOutput, $options);
    }

    /**
     * @return mixed|void
     * @author  David Lopez <dlopez@arisa.cl>
     */
    public function build(Dte $dte, string $fileName, array $extraInfo = [])
    {
        $this->pdf = new PdfDte($options['thermal_paper'] ?? false);
        if (!empty($this->options['logo'])) {
            $this->pdf->setLogo($this->options['logo']);
        }
        if (!empty($extraInfo['caratula'])) {
            $this->pdf->setResolucion([
                'FchResol' => $this->options['cartula']['FchResol'] ?? '',
                'NroResol' => $this->options['cartula']['NroResol'] ?? ''
            ]);
        }
        if ($this->options['cedible']) {
            $this->pdf->setCedible($this->options['cedible']);
        }
        $this->pdf->agregar($dte->getDatos(), $this->options['without_timbre'] ? null : $dte->getTED());
        $this->pdf->Output($this->dirOuput . '/dte_' . $fileName . '_' . $dte->getID(true) . '.pdf', 'F');
    }
}
