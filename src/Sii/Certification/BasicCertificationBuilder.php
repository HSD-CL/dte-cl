<?php
/**
 * @author David Lopez <dleo.lopez@gmail.com>
 * @version 6/8/20 3:23 p. m.
 */

namespace HSDCL\DteCl\Sii\Certification;

use HSDCL\DteCl\Util\Exception;
use phpDocumentor\Reflection\Types\Boolean;
use sasco\LibreDTE\File;
use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Sii\Dte;
use sasco\LibreDTE\Sii\EnvioDte;
use sasco\LibreDTE\Sii\Folios;
use \HSDCL\DteCl\Sii\Base\Dte as HsdDte;

/**
 * Class BasicCertificationBuilder
 * Funciones para la certificación del set de pruebas
 *
 * @package HSDCL\DteCl\Sii\CertificationBuilder
 * @author David Lopez <dleo.lopez@gmail.com>
 */
class BasicCertificationBuilder extends CertificationBuilder
{
    /**
     * @param FirmaElectronica $firma
     * @param array $folios
     * @param \HSDCL\DteCl\Sii\Certification\Source $source
     * @param array $issuing
     * @param array $receiver
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function __construct(FirmaElectronica $firma, Source $source, array $folios = null, array $issuing = null, array $receiver = null)
    {
        parent::__construct($firma, $source, $folios, $issuing, $receiver);
    }

    /**
     * @param array $startFolio
     * @return $this|CertificationBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function parse(array $startFolio = null): CertificationBuilder
    {
        $this->parsed = $this->source->getCases($startFolio);

        return $this;
    }

    /**
     * Funcion que generar cada DTE, timbrar, firmar y agregar al sobre de EnvioDTE
     * @param int|null $startFolio
     * @return bool
     * @throws Exception
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function setStampAndSign(array $startFolio = null): CertificationBuilder
    {
        $this->agent = new EnvioDte();
        foreach ($this->parsed as $document) {
            # TODO Utilizar una estrategia como prototype para validar que el documento parseado
            # sea standar al diseño
            $typeDte = $document['Encabezado']['IdDoc']['TipoDTE'];
            # Agregar emisor
            $document['Encabezado']['Emisor'] = $this->issuing;
            # Agregar el receptor
            $document['Encabezado']['Receptor'] = $this->receiver;
            # TODO Agregar emisor
            if (!empty($startFolio)) {
                $document['Encabezado']['IdDoc']['Folio'] = $startFolio[$typeDte] ?: 0;
                $startFolio[$typeDte]++;
            }
            $dte = new Dte($document);
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
     * @return $this|CertificationBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function setSign(): CertificationBuilder
    {
        $this->agent->setFirma($this->firma);

        return $this;
    }

    /**
     * @param array $caratula
     * @return $this|CertificationBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function setCaratula(array $caratula): CertificationBuilder
    {
        # TODO Investigar formato caratula correcta
        $this->agent->setCaratula($caratula);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function send(): bool
    {
        return $this->agent->enviar();
    }

    /**
     * @param array $startFolio
     * @param array $caratula
     * @return CertificationBuilder
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function build(array $startFolio, array $caratula): CertificationBuilder
    {
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
    public static function exportToPdf(string $filename, string $logoFileName = '../../../resources/assets/img/logo.png', string $dirOutput = '/tmp/set_prueba/'): bool
    {
        # Read the XML
        $agent = new EnvioDte();
        $agent->loadXML(file_get_contents($filename));
        # Prepare the directory
        if (is_dir($dirOutput)) {
            File::rmdir($dirOutput);
        }
        if (!mkdir($dirOutput)) {
            throw new Exception("No se pudo crear el directorio temporal para DTEs");
        }
        # Transform to PDF
        $caratula = $agent->getCaratula();
        foreach ($agent->getDocumentos() as $dte) {
            if (!$dte->getDatos()) {
                throw new Exception("No se pudo obtener los datos del DTE");
            }
            $pdf = new \sasco\LibreDTE\Sii\Dte\PDF\Dte(false);
            $pdf->setFooterText();
            $pdf->setLogo($logoFileName);
            $pdf->setResolucion([
                    'FchResol' => $caratula['FchResol'],
                    'NroResol' => $caratula['NroResol']]
            );
            $pdf->agregar($dte->getDatos(), $dte->getTED());
            $pdf->Output($dirOutput . '/dte_' . $caratula['RutEmisor'] . '_' . $dte->getID() . '.pdf', 'F');
        }
        # Export to director
        File::compress($dirOutput, [
            'format' => 'zip',
            'delete' => true,
            'download' => false
        ]);

        return true;
    }
}
