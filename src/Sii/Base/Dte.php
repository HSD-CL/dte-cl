<?php
/**
 * @version 5/8/20 12:36 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */


namespace HSDCL\DteCl\Sii\Base;

/**
 * Interface Dte
 *
 * Esta interface sirve para contrato un DTE
 * @author  David Lopez <dleo.lopez@gmail.com>
 * @package App
 */
interface Dte
{
    /**
     * Forma de Pago Contado
     */
    const FORMA_PAGO_CONTADO = 1;

    /**
     * Forma de Pago Credito
     */
    const FORMA_PAGO_CREDITO = 2;

    /**
     * Forma de Pago Sin Costo
     */
    const FORMA_PAGO_SIN_COSTO = 3;

    /**
     * Efectivo
     */
    const METODO_PAGO_EFECTIVO = 'EF';

    /**
     * Depósito o transferencia
     */
    const METODO_PAGO_DEPOSITO = 'PE';

    /**
     * Tarjeta de crédito o débito
     */
    const METODO_PAGO_TARJETA = 'TC';

    /**
     * Cheque
     */
    const METODO_PAGO_CHEQUE = 'CH';

    /**
     * Cheque a fecha
     */
    const METODO_PAGO_CHEQUE_FECHA = 'CF';

    /**
     * Letra
     */
    const METODO_PAGO_LETRA = 'LT';

    /**
     * Otro
     */
    const METODO_PAGO_OTRO = 'OT';

    /**
     * Metodo converToArray
     *
     * Este metodo debera convertir el dte al formato estandar de un Dte
     * @return mixed
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function convertToArray(): array;

    const FACTURA_INICIO = 29;

    /**
     * @const
     */
    const FACTURA = 30;

    /**
     * @const
     */
    const FACTURA_EXENTA = 32;

    /**
     * @const
     */
    const FACTURA_ELECTRONICA = 33;

    /**
     * @const
     */
    const FACTURA_EXENTA_ELECTRONICA = 34;

    /**
     * @const
     */
    const BOLETA = 35;

    /**
     * @const
     */
    const BOLETA_EXENTA = 38;

    /**
     * @const
     */
    const BOLETA_ELECTRONICA = 39;

    /**
     * @const
     */
    const LIQUIDACION_FACTURA = 40;

    /**
     * @const
     */
    const BOLETA_NO_AFECTA = 41;

    /**
     * @const
     */
    const LIQUIDACION_FACTURA_ELECTRONICA = 43;

    /**
     * @const
     */
    const FACTURA_DE_COMPRA = 45;

    /**
     * @const
     */
    const FACTURA_DE_COMPRA_ELECTRONICA = 46;

    /**
     * @const
     */
    const COMPROBANTE_PAGO_ELECTRONICO = 48;

    /**
     * @const
     */
    const GUIA_DE_DESPACHO = 50;

    /**
     * @const
     */
    const GUIA_DE_DESPACHO_ELECTRONICA = 52;

    /**
     * @const
     */
    const NOTA_DE_DEBITO = 55;

    /**
     * @const
     */
    const NOTA_DE_DEBITO_ELECTRONICA = 56;

    /**
     * @const
     */
    const NOTA_DE_CREDITO = 60;

    /**
     * @const
     */
    const NOTA_DE_CREDITO_ELECTRONICA = 61;

    /**
     * @const
     */
    const BOLETA_ELECTRONICA_HONORARIOS = 66;


    /**
     * @const
     */
    const FACTURA_DE_EXPORTACION = 110;

    /**
     * @const
     */
    const NOTA_DE_DEBITO_DE_EXPORTACION = 111;

    /**
     * @const
     */
    const NOTA_DE_CREDITO_DE_EXPORTACION = 112;

    /**
     * @const
     */
    const ORDEN_DE_COMPRA = 801;

    /**
     * @const
     */
    const NOTA_DE_PEDIDO = 802;

    /**
     * @const
     */
    const CONTRATO = 803;

    /**
     * @const
     */
    const RESOLUCION = 804;

    /**
     * @const
     */
    const PROCESO_CHILECOMPRA = 805;

    /**
     * @const
     */
    const FICHA_CHILECOMPRA = 806;

    /**
     * @const
     */
    const DUS = 807;

    /**
     * @const
     */
    const  CONOCIMIENTO_DE_EMBARQUE = 808;

    /**
     * Air Will Bill
     */
    const  AWB = 809;

    /**
     * @const
     */
    const MIC_DTA = 810;

    /**
     * @const
     */
    const CARTA_DE_PORTE = 811;

    /**
     * @const
     */
    const  RESOLUCION_SNA = 812;

    /**
     * @const
     */
    const  PASAPORTE = 813;

    /**
     * @const
     */
    const  CERTIFICADO_DE_DEPOSITO_BOLSA = 814;

    /**
     * @const
     */
    const  VALE_DE_PRENDA_BOLSA_PROD_CHILE = 815;

    /**
     * @const
     * @see https://www4.sii.cl/complementoscvui/services/data/facadeServiceCompCompraService/obtieneTiposDocumento
     */
    const TPO_DOC_REF = [
        self::FACTURA_INICIO                  => 'FACTURA DE INICIO',
        self::FACTURA                         => "FACTURA",
        self::FACTURA_EXENTA                  => "FACTURA EXENTA",
        self::FACTURA_ELECTRONICA             => "FACTURA ELECTRONICA",
        self::FACTURA_EXENTA_ELECTRONICA      => "FACTURA EXENTA ELECTRONICA",
        self::BOLETA                          => "BOLETA",
        self::BOLETA_EXENTA                   => "BOLETA EXENTA",
        self::BOLETA_ELECTRONICA              => "BOLETA ELECTRONICA",
        self::BOLETA_ELECTRONICA_HONORARIOS   => "BOLETA DE HONORARIOS ELECTRONICA",
        self::LIQUIDACION_FACTURA             => "LIQUIDACION FACTURA",
        self::LIQUIDACION_FACTURA_ELECTRONICA => "LIQUIDACION FACTURA ELECTRONICA",
        self::BOLETA_NO_AFECTA                => "BOLETA NO AFECTA O EXENTA ELECTRONICA",
        self::FACTURA_DE_COMPRA               => "FACTURA DE COMPRA",
        self::FACTURA_DE_COMPRA_ELECTRONICA   => "FACTURA DE COMPRA ELECTRONICA",
        self::COMPROBANTE_PAGO_ELECTRONICO    => "COMPROBANTE DE PAGO ELECTRÓNICO'",
        self::GUIA_DE_DESPACHO                => "GUIA DE DESPACHO",
        self::GUIA_DE_DESPACHO_ELECTRONICA    => "GUIA DE DESPACHO ELECTRONICA",
        self::NOTA_DE_DEBITO                  => "NOTA DE DEBITO",
        self::NOTA_DE_DEBITO_ELECTRONICA      => "NOTA DE DEBITO ELECTRONICA",
        self::NOTA_DE_CREDITO                 => "NOTA DE CREDITO",
        self::NOTA_DE_CREDITO_ELECTRONICA     => "NOTA DE CREDITO ELECTRONICA",
        self::FACTURA_DE_EXPORTACION          => "FACTURA DE EXPORTACION",
        self::NOTA_DE_DEBITO_DE_EXPORTACION   => "NOTA DE DEBITO DE EXPORTACION",
        self::NOTA_DE_CREDITO_DE_EXPORTACION  => "NOTA DE CREDITO DE EXPORTACION",
        self::ORDEN_DE_COMPRA                 => "ORDEN DE COMPRA",
        self::NOTA_DE_PEDIDO                  => "NOTA DE PEDIDO",
        self::CONTRATO                        => "CONTRATO",
        self::RESOLUCION                      => "RESOLUCIÓN",
        self::PROCESO_CHILECOMPRA             => "PROCESO CHILECOMPRA",
        self::FICHA_CHILECOMPRA               => "Ficha ChileCompra",
        self::DUS                             => "DUS",
        self::CONOCIMIENTO_DE_EMBARQUE        => "B/L (CONOCIMIENTO DE EMBARQUE)",
        self::AWB                             => "AWB (Air Will Bill)",
        self::MIC_DTA                         => "MIC/DTA",
        self::CARTA_DE_PORTE                  => "CARTA DE PORTE",
        self::RESOLUCION_SNA                  => "RESOLUCIÓN DEL SNA DONDE CALIFICA SERVICIOS DE EXPORTACIÓN",
        self::PASAPORTE                       => "PASAPORTE",
        self::CERTIFICADO_DE_DEPOSITO_BOLSA   => "CERTIFICADO DE DEPÓSITO BOLSA PROD. CHILE.",
        self::VALE_DE_PRENDA_BOLSA_PROD_CHILE => "VALE DE PRENDA BOLSA PROD. CHILE",
    ];

    /**
     * Anula Documento de Referencia
     */
    const COD_REFERENCIA_ANULA = 1;

    /**
     * Corrige Texto Documento de NUM Referencia
     */
    const COD_REFERENCIA_CORRIGE = 2;

    /**
     * Corrige montos
     */
    const COD_REFERENCIA_CORRIGE_MONTOS = 3;

    /**
     * Traduccion de los codigos de referencia
     */
    const COD_REFERENCIA = [
        self::COD_REFERENCIA_CORRIGE_MONTOS => "CORRIGE MONTOS",
        self::COD_REFERENCIA_ANULA          => "ANULA DOCUMENTO DE REFERENCIA",
        self::COD_REFERENCIA_CORRIGE        => "CORRIGE TEXTO DOCUMENTO DE NUM REFERENCIA"
    ];
}
