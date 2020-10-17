<?php
/**
 * @author David Lopez <dleo.lopez@gmail.com>
 * @version 24/8/20 3:55 p. m.
 */

namespace HSDCL\DteCl\Sii\Certification;

use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Sii\LibroCompraVenta;

/**
 * Class PurchaseBookCertificactionBuilder
 * Clase para manejar la certificacion del libro de compras
 * @package HSDCL\DteCl\Sii\Certification
 * @author David Lopez <dleo.lopez@gmail.com>
 */
class PurchaseBookCertificactionBuilder extends CertificationBuilder
{
    /**
     * @var array
     */
    protected $caratula;

    /**
     * PurchaseBookCertificactionBuilder constructor.
     * @param FirmaElectronica $firma
     * @param array $folios
     * @param Source $source
     * @param array $issuing
     * @param array $receiver
     */
    public function __construct(FirmaElectronica $firma, Source $source, array $folios = null, array $issuing = null, array $receiver = null)
    {
        parent::__construct($firma, $source, $folios, $issuing, $receiver);
        $this->agent = new LibroCompraVenta(true);
    }

    /**
     * @param array $startFolios
     * @return CertificationBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function parse(array $startFolios = null): CertificationBuilder
    {
        // EN FACTURA CON IVA USO COMUN CONSIDERE QUE EL FACTOR DE PROPORCIONALIDAD
        // DEL IVA ES DE 0.60
        /**
        $factor_proporcionalidad_iva = 60;
        $detalles = [
            // FACTURA DEL GIRO CON DERECHO A CREDITO
            [
                'TpoDoc' => 30,
                'NroDoc' => 234,
                'TasaImp' => \sasco\LibreDTE\Sii::getIVA(),
                'FchDoc' => $this->caratula['PeriodoTributario'].'-01',
                'RUTDoc' => '78885550-8',
                'MntNeto' => 58920,
            ],
            // FACTURA DEL GIRO CON DERECHO A CREDITO
            [
                'TpoDoc' => 33,
                'NroDoc' => 32,
                'TasaImp' => \sasco\LibreDTE\Sii::getIVA(),
                'FchDoc' => $this->caratula['PeriodoTributario'].'-01',
                'RUTDoc' => '78885550-8',
                'MntExe' => 10950,
                'MntNeto' => 12350,
            ],
            // FACTURA CON IVA USO COMUN
            [
                'TpoDoc' => 30,
                'NroDoc' => 781,
                'TasaImp' => \sasco\LibreDTE\Sii::getIVA(),
                'FchDoc' => $this->caratula['PeriodoTributario'].'-02',
                'RUTDoc' => '78885550-8',
                'MntNeto' => 30239,
                // Al existir factor de proporcionalidad se calculará el IVAUsoComun.
                // Se calculará como MntNeto * (TasaImp/100) y se añadirá a MntIVA.
                // Se quitará del detalle al armar los totales, ya que no es nodo del detalle en el XML.
                'FctProp' => $factor_proporcionalidad_iva,
            ],
            // NOTA DE CREDITO POR DESCUENTO A FACTURA 234
            [
                'TpoDoc' => 60,
                'NroDoc' => 451,
                'TasaImp' => \sasco\LibreDTE\Sii::getIVA(),
                'FchDoc' => $this->caratula['PeriodoTributario'].'-03',
                'RUTDoc' => '78885550-8',
                'MntNeto' => 2965,
            ],
            // ENTREGA GRATUITA DEL PROVEEDOR
            [
                'TpoDoc' => 33,
                'NroDoc' => 67,
                'TasaImp' => \sasco\LibreDTE\Sii::getIVA(),
                'FchDoc' => $this->caratula['PeriodoTributario'].'-04',
                'RUTDoc' => '78885550-8',
                'MntNeto' => 12509,
                'IVANoRec' => [
                    'CodIVANoRec' => 4,
                    'MntIVANoRec' => round(12509 * (\sasco\LibreDTE\Sii::getIVA()/100)),
                ],
            ],
            // COMPRA CON RETENCION TOTAL DEL IVA
            [
                'TpoDoc' => 46,
                'NroDoc' => 9,
                'TasaImp' => \sasco\LibreDTE\Sii::getIVA(),
                'FchDoc' => $this->caratula['PeriodoTributario'].'-05',
                'RUTDoc' => '78885550-8',
                'MntNeto' => 10819,
                'OtrosImp' => [
                    'CodImp' => 15,
                    'TasaImp' => \sasco\LibreDTE\Sii::getIVA(),
                    'MntImp' => round(10819 * (\sasco\LibreDTE\Sii::getIVA()/100)),
                ],
            ],
            // NOTA DE CREDITO POR DESCUENTO FACTURA ELECTRONICA 32
            [
                'TpoDoc' => 60,
                'NroDoc' => 211,
                'TasaImp' => \sasco\LibreDTE\Sii::getIVA(),
                'FchDoc' => $this->caratula['PeriodoTributario'].'-06',
                'RUTDoc' => '78885550-8',
                'MntNeto' => 9867,
            ],
        ];
        foreach ($detalles as $detalle) {
            $this->agent->agregar($detalle);
        }
        $this->agent->generar();
         */
        $this->agent->agregarComprasCSV($this->source->getInput());

        return $this;
    }

    /**
     * @return bool
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function send(): bool
    {
        $this->agent->enviar();

        return true;
    }

    /**
     * @param array|null $startFolio
     * @return CertificationBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function setStampAndSign(array $startFolio = null): CertificationBuilder
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
        # Se necesita definir para ser usada luego en el parse
        $this->caratula = $caratula;
        # Agregar caratula por el agente
        $this->agent->setCaratula($caratula);

        return $this;
    }

    /**
     * @return CertificationBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function setSign(): CertificationBuilder
    {
        $this->agent->setFirma($this->firma);

        return $this;
    }

    /**
     * @param array $startFolio
     * @param array $caratula
     * @return CertificationBuilder
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function build(array $startFolio, array $caratula): CertificationBuilder
    {
        $this->parse()
            ->setCaratula($caratula);
        # Generar XML sin firma
        $this->agent->generar();
        $this->setStampAndSign();

        return $this;
    }

    /**
     * @param string $filename
     * @return mixed|void
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function export(string $filename)
    {
        $doc = new \DOMDocument();
        $doc->loadXML($this->agent->saveXML());

        return $doc->save($filename);
    }
}
