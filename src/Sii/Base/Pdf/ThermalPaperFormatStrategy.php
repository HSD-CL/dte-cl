<?php
/**
 * @version 2/8/23 8:13 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Sii\Base\Pdf;

use sasco\LibreDTE\Sii\Dte;

/**
 * Class ThermalPaperFormatStrategy
 * @package HSDCL\DteCl\Sii\Base\Pdf
 * @author  David Lopez <dlopez@arisa.cl>
 */
class ThermalPaperFormatStrategy extends FormatStrategy
{
    /**
     * @param string $dirOutput
     * @param string|null $logo
     * @param array $caratula
     * @param bool $cedible
     * @param bool $withoutTimbre
     */
    public function __construct(string $dirOutput, array $options = [])
    {
        $options['thermal_paper'] = true;
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
        $this->pdf->addContinuousPaper80($dte->getDatos(), $this->options['without_timbre'] ? null : $dte->getTED());
        $this->pdf->Output($this->dirOuput . '/dte_' . $fileName . '_' . $dte->getID(true) . '.pdf', 'F');
    }
}
