<?php
/**
 * @version 13/3/23 10:01 a. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Sii\Base;

use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Sii\ConsumoFolio;
use sasco\LibreDTE\Sii\EnvioDte;/**
 * Class RcofBuilder
 * @version 202303131003
 *@author  David Lopez <dleo.lopez@gmail.com>
 * @package HSDCL\DteCl\Sii\Base
 */
class RcofBuilder extends PacketDteBuilder
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
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function parse(array $startFolios): PacketDteBuilder
    {
        # ¿Que esperamos acá? ¿Un array de archivos? Porque se pueden agregar varios
        return $this;
    }

    /**
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
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function setStampAndSign(array $startFolio = null): PacketDteBuilder
    {
        return $this;
    }

    /**
     * @param array $caratula
     * @return PacketDteBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function setCaratula(array $caratula): PacketDteBuilder
    {
        return $this;
    }

    /**
     * @return PacketDteBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function setSign(): PacketDteBuilder
    {
        return $this;
    }

    /**
     * @param array|int|null $startFolio
     * @param array $caratula
     * @return PacketDteBuilder|bool
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function build(array $startFolio, array $caratula): PacketDteBuilder
    {
        return $this;
    }

    /**
     * @param string $filename
     * @return mixed
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function export(string $filename)
    {
        return $this;
    }

    /**
     * @version 13/3/23
     * @author  David Lopez <dleo.lopez@gmail.com>
     */
    public function make(array $xmls, array $typeDocuments, bool $send = false)
    {
        $consumeFolio = new ConsumoFolio();
        $consumeFolio->setFirma($this->firma);
        $consumeFolio->setDocumentos($typeDocuments);
        foreach ($xmls as $xml) {
            $agent = new EnvioDte();
            $agent->loadXML($xml);
            foreach ($agent->getDocumentos() as $dte) {
                $consumeFolio->agregar($dte->getResumen());
            }
        }
        $caratula = $agent->getCaratula();
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
