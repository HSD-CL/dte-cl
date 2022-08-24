<?php
/**
 * @version 3/2/21 5:31 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Sii\Base;

use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * Class JsonSource
 *
 * Clase que sirve para ser la fuente desde un JSON
 * @version 202207211450
 * @author  David Lopez <dleo.lopez@gmail.com>
 * @package HSDCL\DteCl\Sii\Base
 */
class JsonSource implements Source
{
    /**
     * @var array DTE
     */
    protected array $cases;

    /**
     * JsonSource constructor.
     * @param string $cases
     */
    public function __construct(string $cases)
    {
        $decodeCases = json_decode($cases, true);
        # Podríamos identificar si es un key array o indexed array
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        if (array_keys($decodeCases) !== range(0, count($decodeCases) - 1)) {
            # Solo hay un DTE, validamos la forma
            $this->cases[] = $resolver->resolve($decodeCases);

            return;
        }
        # Si hay más de un caso debemos validar caso por caso
        foreach ($decodeCases as $case) {
            $this->cases[] = $resolver->resolve($case);
        }
    }

    /**
     * Desde el string crear los casos
     * @param array $folios
     * @return array
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function getCases(array $folios = [], array $options = []): array
    {
        return $this->cases;
    }

    /**
     * @return mixed
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function getInput()
    {
        return $this->cases;
    }

    /**
     * Configurar la opciones recibidas
     * @version 8/8/22
     * @author  David Lopez <dleo.lopez@gmail.com>
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('Encabezado', function (OptionsResolver $sResolver) {
            $sResolver->setDefault('IdDoc', function (OptionsResolver $ssResolver) {
                $ssResolver->setDefined(['TipoDTE', 'Folio', 'MntPagos', 'TpoTranVenta', 'FchEmis', 'IndNoRebaja',
                                         'TipoDespacho', 'IndTraslado', 'TpoImpresion', 'IndServicio', 'MntBruto',
                                         'TpoTranCompra', 'TpoTranVenta', 'FmaPago', 'FmaPagExp', 'MntCancel',
                                         'SaldoInsol', 'FchCancel', 'MntPagos', 'PeriodoDesde', 'PeriodoHasta',
                                         'MedioPago', 'TpoCtaPago', 'NumCtaPago', 'BcoPago', 'TermPagoCdg',
                                         'TermPagoGlosa', 'TermPagoDias', 'FchVenc']);
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
        });
        $resolver->setDefault('Detalle', function (OptionsResolver $sResolver) {
            $sResolver->setPrototype(true)
                ->setDefined(['NroLinDet', 'CdgItem', 'IndExe', 'Retenedor', 'NmbItem', 'DscItem', 'QtyRef', 'UnmdRef',
                              'PrcRef', 'QtyItem', 'Subcantidad', 'FchElabor', 'FchVencim', 'UnmdItem', 'PrcItem',
                              'DescuentoPct', 'DescuentoMonto', 'RecargoPct', 'RecargoMonto', 'CodImpAdic',
                              'MontoItem'])
                ->setRequired(['NmbItem', 'QtyItem', 'PrcItem']);
        });
    }
}
