<?php
/**
 * @version 3/2/21 3:32 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Sii\Certification;


use HSDCL\DteCl\Sii\Base\Dte;
use HSDCL\DteCl\Sii\Base\Source;
use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Sii\EnvioDte;
use HSDCL\DteCl\Sii\Base\PacketDteBuilder;

class SimulationBuilder extends BasicCertificationBuilder
{
    /**
     * SimulationBuilder constructor.
     * @param FirmaElectronica $firma
     * @param Source $source
     * @param array|null $folios
     * @param array|null $issuing
     * @param array|null $receiver
     */
    public function __construct(FirmaElectronica $firma, Source $source, array $folios = null, array $issuing = null, array $receiver = null)
    {
        parent::__construct($firma, $source, $folios, $issuing, $receiver);
        $this->agent = new EnvioDte();
    }

    /**
     * @param array $startFolios
     * @return $this|PacketDteBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function parse(array $startFolios = null): PacketDteBuilder
    {
        $this->parsed = $this->source->getCases($startFolios);

        # Agregar los folios
        if (empty($startFolios)) {
            $startFolios = [
                Dte::FACTURA_ELECTRONICA         => 1,
                Dte::NOTA_DE_DEBITO_ELECTRONICA  => 1,
                Dte::NOTA_DE_CREDITO_ELECTRONICA => 1,
                Dte::FACTURA_EXENTA_ELECTRONICA  => 1,
            ];
        }
        foreach ($this->parsed as $i => $document) {
            switch ($document['Encabezado']['IdDoc']['TipoDTE']) {
                case Dte::FACTURA_ELECTRONICA:
                    $document['Encabezado']['IdDoc']['Folio'] = $startFolios[Dte::FACTURA_ELECTRONICA];
                    $startFolios[Dte::FACTURA_ELECTRONICA]++;
                    break;
                case Dte::NOTA_DE_DEBITO_ELECTRONICA:
                    $document['Encabezado']['IdDoc']['Folio'] = $startFolios[Dte::NOTA_DE_DEBITO_ELECTRONICA];
                    $startFolios[Dte::NOTA_DE_DEBITO_ELECTRONICA]++;
                    break;
                case Dte::NOTA_DE_CREDITO_ELECTRONICA:
                    $document['Encabezado']['IdDoc']['Folio'] = $startFolios[Dte::NOTA_DE_CREDITO_ELECTRONICA];
                    $startFolios[Dte::NOTA_DE_CREDITO_ELECTRONICA]++;
                    break;
                case Dte::FACTURA_EXENTA_ELECTRONICA:
                    $document['Encabezado']['IdDoc']['Folio'] = $startFolios[Dte::FACTURA_EXENTA_ELECTRONICA];
                    $startFolios[Dte::FACTURA_EXENTA_ELECTRONICA]++;
                    break;
            }
            $this->parsed[$i] = $document;
        }

        return $this;
    }
}
