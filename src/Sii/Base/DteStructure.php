<?php
/**
 * @version 29/9/22 4:25 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Sii\Base;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class StructureDte
 *
 * Clase que definira la estructura en forma de array nativa de un DTE
 * @version 29/9/22
 * @author  David Lopez <dleo.lopez@gmail.com>
 * @package HSDCL\DteCl\Sii\Base
 */
class DteStructure extends \ArrayObject
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
        if (empty($options['Referencia'])) {
            unset($options['Referencia']);
        }
        if (empty($options['DscRcgGlobal'])) {
            unset($options['DscRcgGlobal']);
        }
        parent::__construct($options, $flags, $iteratorClass);
    }

    /**
     * Configurar la opciones recibidas
     * @version 8/8/22
     * @author  David Lopez <dleo.lopez@gmail.com>
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('Encabezado', function (OptionsResolver $sResolver) {
            $sResolver->setDefined(['OtraMoneda']);
            $sResolver->setDefault('IdDoc', function (OptionsResolver $ssResolver) {
                $ssResolver->setDefined(['TipoDTE', 'Folio', 'MntPagos', 'TpoTranVenta', 'FchEmis', 'IndNoRebaja',
                                         'TipoDespacho', 'IndTraslado', 'TpoImpresion', 'IndServicio', 'MntBruto',
                                         'TpoTranCompra', 'TpoTranVenta', 'FmaPago', 'FmaPagExp', 'MntCancel',
                                         'SaldoInsol', 'FchCancel', 'MntPagos', 'PeriodoDesde', 'PeriodoHasta',
                                         'MedioPago', 'TpoCtaPago', 'NumCtaPago', 'BcoPago', 'TermPagoCdg',
                                         'TermPagoGlosa', 'TermPagoDias', 'FchVenc', 'TipoRetencion', 'IndMntNeto']);
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
                $ssResolver->setRequired(['RUTRecep']);
            });
            $sResolver->setDefault('Transporte', function (OptionsResolver $ssResolver) {
                $ssResolver->setDefined(['Patente', 'RUTTrans', 'Chofer', 'DirDest', 'CmnaDest', 'Aduana']);
            });
            $sResolver->setDefault('Totales', function (OptionsResolver $ssResolver) {
                $ssResolver->setDefined([
                    'MntNeto',
                    'TasaIVA',
                    'IVA',
                    'MntTotal',
                    'TpoMoneda',
                    'MntExe',
                    'MntBase',
                    'MntMargenCom',
                    'IVAProp',
                    'IVATerc',
                    'IVANoRet',
                    'CredEC',
                    'GrntDep',
                    'ValComNeto',
                    'ValComExe',
                    'ValComIVA',
                    'MontoNF',
                    'MontoPeriodo',
                    'SaldoAnterior',
                    'VlrPagar'
                ]);
                $ssResolver->setDefault('ImptoReten', function (OptionsResolver $sssResolver) {
                    $sssResolver->setPrototype(true)
                        ->setDefined(['TipoImp', 'TasaImp', 'MontoImp']);
                });
            });
        });
        $resolver->setDefault('Detalle', function (OptionsResolver $sResolver) {
            $sResolver->setPrototype(true)
                ->setDefined(['NroLinDet', 'CdgItem', 'IndExe', 'Retenedor', 'NmbItem', 'DscItem', 'QtyRef', 'UnmdRef',
                              'PrcRef', 'QtyItem', 'Subcantidad', 'FchElabor', 'FchVencim', 'UnmdItem', 'PrcItem',
                              'DescuentoPct', 'DescuentoMonto', 'RecargoPct', 'RecargoMonto', 'CodImpAdic',
                              'MontoItem'])
                ->setRequired(['NmbItem']);
        });
        $resolver->setDefault('Referencia', function (OptionsResolver $sResolver) {
            $sResolver->setPrototype(true)
                ->setDefined(['NroLinRef', 'TpoDocRef', 'IndGlobal', 'FolioRef', 'RUTOtr', 'IdAdicOtr', 'FchRef',
                              'CodRef', 'RazonRef']);
        });

        $resolver->setDefault('DscRcgGlobal', function (OptionsResolver $sResolver) {
            $sResolver->setPrototype(true)
                ->setDefined(['TpoMov', 'TpoValor', 'ValorDR', 'IndExeDR']);
        });
    }
}
