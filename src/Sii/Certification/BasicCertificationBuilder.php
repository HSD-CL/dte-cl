<?php
/**
 * @version 6/8/20 3:23 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Sii\Certification;

use HSDCL\DteCl\Sii\Base\PacketDteBuilder;
use HSDCL\DteCl\Sii\Base\Source;
use HSDCL\DteCl\Util\Exception;
use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Log;
use sasco\LibreDTE\Sii\Dte;
use sasco\LibreDTE\Sii\EnvioDte;
use HSDCL\DteCl\Sii\Factory\PdfFileFactory;

/**
 * Class BasicCertificationBuilder
 * Funciones para la certificación del set de pruebas
 *
 * @package HSDCL\DteCl\Sii\PacketDteBuilder
 * @author  David Lopez <dleo.lopez@gmail.com>
 */
class BasicCertificationBuilder extends PacketDteBuilder
{
    /**
     * @param FirmaElectronica $firma
     * @param array $folios
     * @param Source $source
     * @param array $issuing
     * @param array $receiver
     * @author David Lopez <dlopez@hsd.cl>
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

        return $this;
    }

    /**
     * Funcion que generar cada DTE, timbrar, firmar y agregar al sobre de EnvioDTE
     * @param int|null $startFolio
     * @return bool
     * @throws Exception
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function setStampAndSign(array $startFolio = null): PacketDteBuilder
    {
        foreach ($this->parsed as $document) {
            # TODO Utilizar una estrategia como prototype para validar que el documento parseado
            # sea standar al diseño
            $typeDte = $document['Encabezado']['IdDoc']['TipoDTE'];
            # Agregar emisor
            $document['Encabezado']['Emisor'] = $this->issuing;
            # Agregar el receptor
            $document['Encabezado']['Receptor'] = array_merge(
                $this->receiver,
                empty($document['Encabezado']['Receptor']) ? [] : $document['Encabezado']['Receptor']
            );
            # TODO Agregar emisor
            if (!empty($startFolio)) {
                $document['Encabezado']['IdDoc']['Folio'] = $startFolio[$typeDte] ?: 0;
                $startFolio[$typeDte]++;
            }
            $dte = new Dte($document);
            if (!$dte->timbrar($this->folios[$typeDte])) {
                $error = Log::read();
                throw new Exception('No se pudo timbrar el dte. Razón ' . $error);
            }
            if (!$dte->firmar($this->firma)) {
                $error = Log::read();
                throw new Exception('No se pudo firmar el dte. Razón ' . $error);
            }
            if (!$this->agent->agregar($dte)) {
                $error = Log::read();
                throw new Exception('No se pudo agregar el dte. Razón ' . $error);
            };
        }

        return $this;
    }

    /**
     * @return $this|PacketDteBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function setSign(): PacketDteBuilder
    {
        $this->agent->setFirma($this->firma);

        return $this;
    }

    /**
     * @param array $caratula
     * @return $this|PacketDteBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function setCaratula(array $caratula): PacketDteBuilder
    {
        # TODO Investigar formato caratula correcta
        $this->agent->setCaratula($caratula);

        return $this;
    }

    /**
     * This will return the track id
     * @return mixed Track id, false in case of failure
     */
    public function send()
    {
        return $this->agent->enviar();
    }

    /**
     * @param array $startFolio
     * @param array $caratula
     * @return PacketDteBuilder
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function build(array $startFolio, array $caratula): PacketDteBuilder
    {
        # TODO Validation all required is defined
        $this->parse($startFolio)
            ->setStampAndSign($startFolio)
            ->setCaratula($caratula)
            ->setSign();

        return $this;
    }

    /**
     * @param string $filename
     * @return false|int
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function export(string $filename)
    {
        return file_put_contents($filename, $this->agent->generar());
    }

    /**
     * @param string $filename
     * @param string $logoFileName
     * @param string $dirOutput
     * @throws Exception
     * @author David Lopez <dlopez@hsd.cl>
     */
    public static function exportToPdf(string $filename, string $logoFileName = __DIR__ . '/../../../resources/assets/img/logo.png', string $dirOutput = '/tmp/set_prueba/'): bool
    {
        try {
            return PdfFileFactory::make($filename, $dirOutput, $logoFileName);
        } catch (\HSDCL\DteCl\Exception $e) {
            return false;
        }
    }
}
