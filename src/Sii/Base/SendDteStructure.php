<?php
/**
 * @version 30/9/22 6:08 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Sii\Base;

use Symfony\Component\OptionsResolver\OptionsResolver;/**
 * Class SendDteStructure
 *
 * Clase que definirá la estrucutra en forma de un array nativo de un EnvioDte
 * @version 30/9/22
 *@author  David Lopez <dleo.lopez@gmail.com>
 * @package HSDCL\DteCl\Sii\Base
 */
class SendDteStructure extends \ArrayObject
{
    /**
     * Constructor de la clase
     * @param array $array
     * @param int $flags
     * @param string $iteratorClass
     */
    public function __construct($array = [], $flags = 0, $iteratorClass = "ArrayIterator")
    {
        # Podríamos identificar si es un key array o indexed array
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $options = $resolver->resolve($array);
        parent::__construct($options, $flags, $iteratorClass);
    }

    /**
     * Configurar la opciones recibidas
     * @version 8/8/22
     * @author  David Lopez <dleo.lopez@gmail.com>
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('EnvioDTE', function (OptionsResolver $resolverI) {
            $resolverI->setDefault('SetDTE', function (OptionsResolver $resolverII) {
                $resolverII->setDefault('Caratula', function (OptionsResolver $resolverIII) {
                    $resolverIII->setRequired(['RutEmisor', 'RutEnvia', 'RutReceptor', 'FchResol', 'NroResol', 'TmstFirmaEnv', 'SubTotDTE']);
                });
                $resolverII->setDefault('DTE', function (OptionsResolver $resolverIII) {
                    $resolverIII->setDefault('Encabezado', function (OptionsResolver $sResolver) {
                        $sResolver->setDefault('IdDoc', function (OptionsResolver $ssResolver) {
                            $ssResolver->setDefined(['TipoDTE', 'Folio', 'MntPagos', 'TpoTranVenta', 'FchEmis', 'IndNoRebaja',
                                                     'TipoDespacho', 'IndTraslado', 'TpoImpresion', 'IndServicio', 'MntBruto',
                                                     'TpoTranCompra', 'TpoTranVenta', 'FmaPago', 'FmaPagExp', 'MntCancel',
                                                     'SaldoInsol', 'FchCancel', 'MntPagos', 'PeriodoDesde', 'PeriodoHasta',
                                                     'MedioPago', 'TpoCtaPago', 'NumCtaPago', 'BcoPago', 'TermPagoCdg',
                                                     'TermPagoGlosa', 'TermPagoDias', 'FchVenc', 'TipoRetencion']);
                            $ssResolver->setRequired(['TipoDTE', 'Folio']);
                        });
                        $sResolver->setDefault('Emisor', function (OptionsResolver $ssResolver) {
                            $ssResolver->setDefined(['RUTEmisor', 'RznSoc', 'GiroEmis', 'Telefono', 'CorreoEmisor', 'Acteco',
                                                     'GuiaExport', 'Sucursal', 'CdgSIISucur', 'DirOrigen', 'CmnaOrigen',
                                                     'CiudadOrigen', 'CdgVendedor', 'IdAdicEmisor']);
                            $ssResolver->setRequired(['RUTEmisor', 'RznSoc', 'GiroEmis', 'Acteco', 'DirOrigen', 'CmnaOrigen']);
                        });
                        $sResolver->setDefault('Receptor', function (OptionsResolver $ssResolver) {
                            $ssResolver->setDefined(['RUTRecep', 'CdgIntRecep', 'RznSocRecep', 'Extranjero', 'GiroRecep',
                                                     'Contacto', 'CorreoRecep', 'DirRecep', 'CmnaRecep', 'CiudadRecep',
                                                     'DirPostal', 'CmnaPostal', 'CiudadPostal']);
                            $ssResolver->setRequired(['RUTRecep', 'RznSocRecep', 'GiroRecep', 'DirRecep', 'CmnaRecep']);
                        });
                        $sResolver->setDefault('Totales', function (OptionsResolver $ssResolver) {
                            $ssResolver->setRequired(['MntNeto', 'TasaIVA', 'IVA', 'MntTotal']);
                        });
                    });
                    $resolverIII->setDefault('Detalle', function (OptionsResolver $sResolver) {
                        $sResolver->setPrototype(true)
                            ->setDefined(['NroLinDet', 'CdgItem', 'IndExe', 'Retenedor', 'NmbItem', 'DscItem', 'QtyRef', 'UnmdRef',
                                          'PrcRef', 'QtyItem', 'Subcantidad', 'FchElabor', 'FchVencim', 'UnmdItem', 'PrcItem',
                                          'DescuentoPct', 'DescuentoMonto', 'RecargoPct', 'RecargoMonto', 'CodImpAdic',
                                          'MontoItem'])
                            ->setRequired(['NmbItem', 'QtyItem', 'PrcItem']);
                    });
                })->setPrototype(true);
            });
        });

    }
}
