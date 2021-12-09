<?php
/**
 * @version 11/11/21 5:20 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Sii\Base;


use HSDCL\DteCl\Util\Exception;
use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Sii;
use sasco\LibreDTE\Sii\Dte;
use sasco\LibreDTE\Sii\EnvioDte;

class DteBuilder extends PacketDteBuilder
{
    /**
     * @var bool
     */
    private $normalizar;

    /**
     * @param FirmaElectronica $firma
     * @param array $folios
     * @param \HSDCL\DteCl\Sii\Certification\Source $source
     * @param array $issuing
     * @param array $receiver
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function __construct(FirmaElectronica $firma, Source $source, array $folios = null, array $issuing = null,
                                array $receiver = null, int $environment = Sii::CERTIFICACION, bool $normalizar = true)
    {
        $tmpFolios = [];
        # Process folios
        foreach ($folios as $key => $folio) {
            $tmpFolios[$key] = new Sii\Folios($folio);
        }
        parent::__construct($firma, $source, $tmpFolios, $issuing, $receiver, $environment);
        $this->normalizar = $normalizar;
        $this->agent = new EnvioDte();
    }

    /**
     * @param array $startFolios
     * @return PacketDteBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function parse(array $startFolios = null): PacketDteBuilder
    {
        $this->parsed = $this->source->getCases($startFolios);

        return $this;
    }

    /**
     * Enviara el DTE a Sii
     * @return false|mixed|Sii\Track
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function send(int $retry = null, bool $gzip = false)
    {
        return $this->agent->enviar($retry, $gzip);
    }

    /**
     * Firma y estampar el(los) dte(s)
     * @param array|null $startFolio
     * @return PacketDteBuilder
     * @throws Exception
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function setStampAndSign(array $startFolio = null): PacketDteBuilder
    {
        foreach ($this->parsed as $document) {
            $typeDte = $document['Encabezado']['IdDoc']['TipoDTE'];
            $dte = new Dte($document, $this->normalizar);
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
     * Agregar la caratula al envio dte
     * @param array $caratula
     * @return PacketDteBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function setCaratula(array $caratula): PacketDteBuilder
    {
        # TODO Validar que al menos esten los parametros minimos
        $dte = $this->parsed[0];
        $caratula = array_merge(
            ['RutEnvia' => $this->firma->getID(), 'RutReceptor' => $dte['Encabezado']['Receptor']['RUTRecep']],
            $caratula
        );
        # Validar caratula
        $this->agent->setCaratula($caratula);

        return $this;
    }

    /**
     * Firmar el envio dte
     * @return PacketDteBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function setSign(): PacketDteBuilder
    {
        $this->agent->setFirma($this->firma);

        return $this;
    }

    /**
     * @param array $startFolio
     * @param array $caratula
     * @return PacketDteBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function build(array $startFolio = [], array $caratula): PacketDteBuilder
    {
        $this->parse($startFolio)
            ->setStampAndSign($startFolio)
            ->setCaratula($caratula)
            ->setSign()
        ;

        return $this;
    }

    /**
     * @param string $filename
     * @return mixed|void
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function export(string $filename = null)
    {
        return base64_encode($this->agent->generar());
    }
}
