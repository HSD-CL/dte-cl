<?php

/**
 * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
 * @version 24/8/20 3:55 p. m.
 */

namespace HSDCL\DteCl\Sii\Certification;

use sasco\LibreDTE\Sii\Dte\PDF\Dte;
use sasco\LibreDTE\Sii\EnvioDte;

/**
 * Class ShipmentCertificactionExportPdfBuilder
 * Clase para exportar las guias de despacho a pdf
 * @author Danilo Vasques <dvasquezr.ko@gmail.com>
 */
class ShipmentCertificactionExportPdfBuilder
{
    /**
     * @var array
     */
    protected $caratula;
    protected $DTE;
    public $data;

    /**
     * ShipmentCertificactionExportPdfBuilder constructor.
     * @param string $source
     * @param string $logo
     * @param string $output
     */
    public function __construct(string $source, string $logo, string $output)
    {
        $this->file = $source;
        $this->logo = $logo;
        $this->output = $output;
        $this->agent = new EnvioDte();
    }

    /**
     * @author Danilo Vasquez
     */
    public function parse()
    {
        $this->agent->loadXML(file_get_contents($this->file));
        $this->caratula = $this->agent->getCaratula();
        $this->data = $this->agent->getDocumentos();
        return $this;
    }

    /**
     * @author Danilo Vasquez
     */
    public function getDocument()
    {
        foreach ($this->data as $document) {
            if (!$document->getDatos())
                die('No se pudieron obtener los datos del DTE');

            $pdf = new Dte(false);
            $pdf->setFooterText();
            $pdf->setLogo($this->logo); // debe ser PNG!
            $pdf->setResolucion(['FchResol' => $this->caratula['FchResol'], 'NroResol' => $this->caratula['NroResol']]);
            $pdf->agregar($document->getDatos(), $document->getTED());
            $pdf->Output($this->output . '/dte_' . $this->caratula['RutEmisor'] . '_' . $document->getID() . '.pdf', 'F');
        }
        return $this;
    }

    /**
     * @author Danilo Vasquez <dvasquezr.ko@gmail.com>
     */
    public function build()
    {
        $this->parse();
        $this->getDocument();
        return $this;
    }
}
