<?php
/**
 * @version 9/11/21 4:25 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Sii\Certification;

use HSDCL\DteCl\Sii\Base\PacketDteBuilder;
use HSDCL\DteCl\Sii\Base\Source;
use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Sii\EnvioDte;
use sasco\LibreDTE\Sii\EnvioRecibos;
use sasco\LibreDTE\Sii\RespuestaEnvio;

/**
 * Class StageExchangeReception
 *
 * Clase que genera el XML de recibos para un proveedor, el XML generado
 * deberá ser subido "a mano" a https://www4.sii.cl/pfeInternet
 *
 * @package HSDCL\DteCl\Sii\Certification
 * @author  David Lopez <dleo.lopez@gmail.com>
 */
class StageExchangeSendReceiptBuilder extends PacketDteBuilder
{
    /**
     * @var array
     */
    protected array $caratula;

    /**
     * @var
     */
    protected $xml;

    /**
     * @var bool
     */
    private $isDirty = true;

    /**
     * @version 9/11/21
     * @author  David Lopez <dlopez@hsd.cl>
     */
    public function __construct(FirmaElectronica $firma, Source $source)
    {
        parent::__construct($firma, $source);
        $this->setAgent(new EnvioDte());
    }

    /**
     * @param array $startFolios
     * @return PacketDteBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function parse(array $startFolios = null): PacketDteBuilder
    {
        # Cargar EnvioDTE y extraer arreglo con datos de carátula y DTEs
        $this->getAgent()->loadXML(file_get_contents($this->source->getInput()));
        $this->isDirty = true;

        return $this;
    }

    public function send()
    {
        // TODO: Implement send() method.
    }

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
        # Definimos la caratula
        $this->caratula = $this->getAgent()->getCaratula();
        $this->caratula['RutResponde'] = $caratula['RutResponde'];
        $this->caratula['RutRecibe'] = $this->caratula['RutEmisor'];
        $this->caratula['NmbContacto'] = $caratula['NmbContacto'];
        $this->caratula['MailContacto'] = $caratula['MailContacto'];
        $this->isDirty = true;

        return $this;
    }

    public function setSign(): PacketDteBuilder
    {
        return $this;
    }

    /**
     * @param array $startFolio
     * @param array $caratula
     * @return PacketDteBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function build(array $startFolio = null, array $caratula = null): PacketDteBuilder
    {
        # Objeto EnvioRecibo, asignar carátula y Firma
        $answerSend = new EnvioRecibos();
        # Aqui podríamos usar un especie dirty o clean para saber si la caratula esta
        $answerSend->setCaratula([
            'RutResponde'  => $this->caratula['RutResponde'],
            'RutRecibe'    => $this->caratula['RutRecibe'],
            'NmbContacto'  => $this->caratula['NmbContacto'],
            'MailContacto' => $this->caratula['MailContacto'],
        ]);
        $answerSend->setFirma($this->firma);
        # Procesar cada DTE
        foreach ($this->getAgent()->getDocumentos() as $document) {
            $answerSend->agregar([
                'TipoDoc'   => $document->getTipo(),
                'Folio'     => $document->getFolio(),
                'FchEmis'   => $document->getFechaEmision(),
                'RUTEmisor' => $document->getEmisor(),
                'RUTRecep'  => $document->getReceptor(),
                'MntTotal'  => $document->getMontoTotal(),
                'Recinto'   => 'Oficina central', // ¿Deberá ser parameterizable?
                'RutFirma'  => $this->firma->getID(),
            ]);
        }
        # Generar xml
        $this->xml = $answerSend->generar();

        if (!$answerSend->schemaValidate()) {
            $this->isDirty = true;

            return $this;
        }
        $this->isDirty = false;

        return $this;
    }

    /**
     * @param string $filename
     * @return mixed|void
     * @throws \Exception
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function export(string $filename)
    {
        // TODO Make test
        if ($this->isDirty) {
            throw new \Exception("The builder is dirty, some steps are missing or failure");
        }

        return file_put_contents($filename, $this->xml);
    }
}
