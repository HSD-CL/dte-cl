<?php
/**
 * @version 20/7/22 11:15 a. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Sii\Certification;

use HSDCL\DteCl\Sii\Base\Dte;
use HSDCL\DteCl\Sii\Base\PacketDteBuilder;
use HSDCL\DteCl\Sii\Base\Source;
use HSDCL\DteCl\Util\Exception;
use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Sii\ConsumoFolio;
use sasco\LibreDTE\Sii\EnvioDte;

/**
 * Class TicketCertificationBuilder
 *
 * Clase que proporcionara los metodos para certificar una boleta electronica
 * @version 202207201123
 * @author  David Lopez <dleo.lopez@gmail.com>
 * @package HSDCL\DteCl\Sii\Certification
 */
class TicketCertificationBuilder extends PacketDteBuilder
{
    /**
     * @param FirmaElectronica $firma
     * @param array $folios
     * @param Source $source
     * @param array $issuing
     * @param array $receiver
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function __construct(
        FirmaElectronica $firma,
        Source           $source,
        array            $folios = null,
        array            $issuing = null,
        array            $receiver = null)
    {
        parent::__construct($firma, $source, $folios, $issuing, $receiver);
        $this->agent = new EnvioDte();
    }

    /**
     * @param array $startFolios
     * @return PacketDteBuilder
     * @version 202207201135
     * @author  David Lopez <dleo.lopez@gmail.com>
     */
    public function parse(array $startFolios = null): PacketDteBuilder
    {
        $this->parsed = $this->source->getCases($startFolios, ['separator' => '=========']);

        return $this;
    }

    /**
     * Metodo que enviara el documento al SII
     *
     * @return mixed Track id, false en caso de error
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function send()
    {
        return $this->agent->enviar();
    }

    /**
     * @param array|null $startFolio
     * @return PacketDteBuilder
     * @version 202207201135
     * @author  David Lopez <dleo.lopez@gmail.com>
     */
    public function setStampAndSign(array $startFolio = null): PacketDteBuilder
    {
        foreach ($this->parsed as $document) {
            # Agregar que el tipo dte es boleta
            $document['Encabezado']['IdDoc']['TipoDTE'] = $document['Encabezado']['IdDoc']['TipoDTE'] ?? Dte::BOLETA_ELECTRONICA;
            $typeDte = $document['Encabezado']['IdDoc']['TipoDTE'];
            # Agregar emisor
            $document['Encabezado']['Emisor'] = $this->issuing;
            # Agregar el receptor
            $document['Encabezado']['Receptor'] = array_merge(
                $this->receiver,
                empty($document['Encabezado']['Receptor']) ? [] : $document['Encabezado']['Receptor']
            );
            if (!empty($startFolio)) {
                $document['Encabezado']['IdDoc']['Folio'] = $startFolio[$typeDte] ?? 1;
                $startFolio[$typeDte]++;
            }
            $dte = new \sasco\LibreDTE\Sii\Dte($document);
            if (!$dte->timbrar($this->folios[$typeDte])) {
                throw new Exception('No se pudo timbrar el dte');
            }
            if (!$dte->firmar($this->firma)) {
                throw new Exception('No se pudo firmar el dte');
            }
            if (!$this->agent->agregar($dte)) {
                throw new Exception('No se pudo agregar el dte');
            };
        }

        return $this;
    }

    /**
     * @param array $caratula
     * @return PacketDteBuilder
     * @version 202207201203
     * @author  David Lopez <dleo.lopez@gmail.com>
     */
    public function setCaratula(array $caratula): PacketDteBuilder
    {
        $this->agent->setCaratula($caratula);

        return $this;
    }

    /**
     * Método que pondra la firma
     * @return PacketDteBuilder
     * @version 202207201204
     * @author  David Lopez <dleo.lopez@gmail.com>
     */
    public function setSign(): PacketDteBuilder
    {
        $this->agent->setFirma($this->firma);

        return $this;
    }

    /**
     * Metodo que construirá la certificación
     * @param array|int|null $startFolio
     * @param array $caratula
     * @return PacketDteBuilder|bool
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function build(array $startFolio, array $caratula): PacketDteBuilder
    {
        $this->parse($startFolio)
            ->setStampAndSign($startFolio)
            ->setCaratula($caratula)
            ->setSign();

        return $this;
    }

    /**
     * Método que genera el archivo
     * @param string $filename
     * @return mixed
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function export(string $filename)
    {
        return file_put_contents($filename, $this->agent->generar());
    }

    /**
     * Metodo para construir RCOF
     * Reporte de Consumo de Folios (RCOF) asociado
     * @version 21/7/22
     * @author  David Lopez <dleo.lopez@gmail.com>
     */
    public function buildRcof(string $fileName, bool $send = false)
    {
        $this->agent->loadXML(file_get_contents($fileName));
        $consumeFolio = new ConsumoFolio();
        $consumeFolio->setFirma($this->firma);
        $consumeFolio->setDocumentos([Dte::BOLETA_ELECTRONICA]);
        foreach ($this->agent->getDocumentos() as $dte) {
            $consumeFolio->agregar($dte->getResumen());
        }
        $caratula = $this->agent->getCaratula();
        $consumeFolio->setCaratula([
            'RutEmisor' => $caratula['RutEmisor'],
            'FchResol'  => $caratula['FchResol'],
            'NroResol'  => $caratula['NroResol'],
        ]);
        # Genera, validar y mostrar XML
        $xml = $consumeFolio->generar();
        if (!$xml) {
            return false;
        }
        # Crear respuesta
        $answer = [
            'xml' => $xml
        ];
        if ($consumeFolio->schemaValidate() && $send) {
            $trackId = $consumeFolio->enviar();
            if ($trackId) {
                $answer['track_id'] = $trackId;
            }
        }

        return $answer;
    }
}
