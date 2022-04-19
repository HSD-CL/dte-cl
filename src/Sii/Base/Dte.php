<?php
/**
 * @version 5/8/20 12:36 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */


namespace HSDCL\DteCl\Sii\Base;

/**
 * Interface Dte
 *
 * Esta interface sirve para mapear un DTE
 * @author  David Lopez <dleo.lopez@gmail.com>
 * @package App
 */
interface Dte
{
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
        self::NOTA_DE_CREDITO_DE_EXPORTACION  => "NOTA DE CREDITO DE EXPORTACION"
    ];
}
