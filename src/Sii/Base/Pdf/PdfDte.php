<?php
/**
 * @version 5/2/21 3:37 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Sii\Base\Pdf;

use HSDCL\DteCl\Sii\Base\Dte;

/**
 * Class PdfDte
 *
 * Lo ideal sería que el PDF se creara a partir de un XML
 *
 * @package HSDCL\DteCl\Sii\Base
 * @author  David Lopez <dlopez@arisa.cl>
 */
class PdfDte extends \sasco\LibreDTE\Sii\Dte\PDF\Dte
{
    /**
     * Posición vertical (mm) donde LibreDTE dibuja el pie del documento en
     * papel carta cuando el timbre va al pie: totales, timbre y acuse de
     * recibo. Refleja el valor por defecto del parámetro $y de
     * agregarTotales(), agregarTimbre() y agregarAcuseRecibo().
     */
    const PIE_Y = 190;

    /**
     * Distancia (mm) que LibreDTE resta a PIE_Y para dibujar la observación
     * (glosa, medio y días de pago) por encima de los totales.
     */
    const PIE_OBSERVACION_OFFSET = 15;

    /**
     * Separación mínima (mm) entre el fin de los datos del documento y el
     * inicio del pie.
     */
    const PIE_MARGEN = 2;

    /**
     * Columnas numéricas del detalle cuyo ancho se ajusta al contenido para
     * que el valor no se parta en dos renglones
     */
    const DETALLE_COLUMNAS_AJUSTABLES = ['QtyItem', 'PrcItem', 'DescuentoMonto', 'RecargoMonto', 'MontoItem'];

    /**
     * Ancho máximo (mm) que puede tomar una columna numérica del detalle; el
     * espacio sale de la columna "Item"
     */
    const DETALLE_COLUMNA_ANCHO_MAXIMO = 45;

    /**
     * Holgura (mm) que se suma al ancho medido del texto de una columna
     */
    const DETALLE_COLUMNA_HOLGURA = 1;

    /**
     * Distribución del pie del último documento agregado en papel carta
     *
     * @var array|null
     */
    protected $distribucionPie;

    /**
     * @param \SimpleXMLElement $xml
     * @param array $options
     * @version 5/2/21
     * @author  David Lopez <dlopez@hsd.cl>
     */
    public function __construct($continuousPaper = false, array $options = [], string $encoding = 'ISO-8859-1')
    {
        parent::__construct($continuousPaper);
        $this->SetTitle($options['title'] ?? config('dte-cl.pdf.title', 'ARISA'));
        $this->SetAuthor($options['author'] ?? config('dte-cl.pdf.author', 'ARISA - https://www.arisa.cl'));
        $this->SetCreator($options['creator'] ?? config('dte-cl.pdf.creator', 'ARISA - https://www.arisa.cl'));
        $this->setFooterText($options['footer'] ?? config('dte-cl.pdf.footer', 'ARISA - https://www.arisa.cl'));
        $this->encoding = $encoding;
    }

    /**
     * Método que agrega el documento tributario al PDF. En papel carta usa el
     * flujo propio, que evita que el pie quede sobre el detalle; en papel
     * continuo delega en LibreDTE.
     *
     * @param array $dte Arreglo con los datos del XML (tag Documento)
     * @param string|null $timbre String XML con el tag TED del DTE
     * @author Danilo Vásquez
     * @version 12/11/26
     */
    public function agregar(array $dte, $timbre = null)
    {
        if ((int)$this->papelContinuo !== 0) {
            parent::agregar($dte, $timbre);

            return;
        }
        $this->addLetter($dte, $timbre);
    }

    /**
     * Método que agrega el documento tributario en papel carta sin timbre
     *
     * @param array $dte Arreglo con los datos del XML (tag Documento)
     * @author David Lopez <dlopez@arisa.cl>
     * @version 12/11/26
     */
    public function addWithoutTimbre(array $dte)
    {
        $this->addLetter($dte, null);
    }

    /**
     * Retorna cómo quedó distribuido el pie del último documento agregado en
     * papel carta, o null si aún no se agrega ninguno. Índices:
     *  - pagina: página donde se dibujó el pie
     *  - y_fin_datos: Y (mm) donde terminan los datos en esa página
     *  - y_inicio_pie: Y (mm) donde comienza el pie en esa página
     *  - salto: si se agregó una página solo para el pie
     *
     * @return array|null
     */
    public function getDistribucionPie(): ?array
    {
        return $this->distribucionPie;
    }

    /**
     * Método que agrega una página con el documento tributario en papel
     * contínuo de 80mm
     * @param array Arreglo con los datos del XML (tag Documento)
     * @param timbre String XML con el tag TED del DTE
     * @param width Ancho del papel contínuo en mm (es parámetro porque se usa el mismo método para 75mm)
     * @author David Lopez <dlopez@arisa.cl>
     */
    public function addContinuousPaper80(array $dte, $timbre, $width = 80, $height = 0)
    {
        // si hay logo asignado se usa centrado
        if (!empty($this->logo)) {
            $this->logo['posicion'] = 'C';
        }
        // determinar alto de la página y agregarla
        $x_start = 1;
        $y_start = 1;
        $offset = 16;
        // determinar alto de la página y agregarla
        $this->AddPage('P', [$height ? $height : $this->papel_continuo_alto, $width]);
        // agregar cabecera del documento
        $y = $this->agregarFolio(
            $dte['Encabezado']['Emisor']['RUTEmisor'],
            $dte['Encabezado']['IdDoc']['TipoDTE'],
            $dte['Encabezado']['IdDoc']['Folio'],
            isset($dte['Encabezado']['Emisor']['CmnaOrigen']) ? $dte['Encabezado']['Emisor']['CmnaOrigen'] : 'Sin comuna', // siempre debería tener comuna
            $x_start, $y_start, $width-($x_start*4), 10,
            [0,0,0]
        );
        $y = $this->agregarEmisor($dte['Encabezado']['Emisor'], $x_start, $y+2, $width-($x_start*45), 8, 9, [0,0,0]);
        // datos del documento
        $this->SetY($y);
        $this->Ln();
        $this->setFont('', '', 8);
        $this->agregarDatosEmision($dte['Encabezado']['IdDoc'], !empty($dte['Encabezado']['Emisor']['CdgVendedor'])?$dte['Encabezado']['Emisor']['CdgVendedor']:null, $x_start, $offset, false);
        $this->agregarReceptor($dte['Encabezado'], $x_start, $offset);
        $this->agregarTraslado(
            !empty($dte['Encabezado']['IdDoc']['IndTraslado']) ? $dte['Encabezado']['IdDoc']['IndTraslado'] : null,
            !empty($dte['Encabezado']['Transporte']) ? $dte['Encabezado']['Transporte'] : null,
            $x_start, $offset
        );
        if (!empty($dte['Referencia'])) {
            $this->agregarReferencia($dte['Referencia'], $x_start, $offset);
        }
        $this->Ln();
        /*
        if (!empty($dte['Detalle'])) {
            $this->agregarDetalleContinuo($dte['Detalle']);
        }
        */
        if (!empty($dte['DscRcgGlobal'])) {
            $this->Ln();
            $this->Ln();
            $this->agregarSubTotal($dte['Detalle'], 23, 17);
            $this->agregarDescuentosRecargos($dte['DscRcgGlobal'], 23, 17);
        }
        if (!empty($dte['Encabezado']['IdDoc']['MntPagos'])) {
            $this->Ln();
            $this->Ln();
            $this->agregarPagos($dte['Encabezado']['IdDoc']['MntPagos'], $x_start);
        }
        $OtraMoneda = !empty($dte['Encabezado']['OtraMoneda']) ? $dte['Encabezado']['OtraMoneda'] : null;
        $this->agregarTotales($dte['Encabezado']['Totales'], $OtraMoneda, $this->y+6, 23, 17);
        // agregar acuse de recibo y leyenda cedible
        if ($this->cedible and !in_array($dte['Encabezado']['IdDoc']['TipoDTE'], $this->sinAcuseRecibo)) {
            $this->agregarAcuseReciboContinuo(3, $this->y+6, 68, 34);
            $this->agregarLeyendaDestinoContinuo($dte['Encabezado']['IdDoc']['TipoDTE']);
        }
        // agregar timbre
        $y = $this->agregarObservacion($dte['Encabezado']['IdDoc'], $x_start, $this->y+6);
        $this->agregarTimbre($timbre, -10, $x_start, $y+6, 70, 6);
        // si el alto no se pasó, entonces es con autocálculo, se elimina esta página y se pasa el alto
        // que se logró determinar para crear la página con el alto correcto
        if (!$height) {
            $this->deletePage($this->PageNo());
            $this->addContinuousPaper80($dte, $timbre, $width, $this->getY()+30);
        }
    }

    /**
     * Método que agrega el documento tributario en papel carta. Replica
     * agregar_papel_0() de LibreDTE (que es privado), pero antes de dibujar
     * el pie comprueba si los datos del documento invaden su zona.
     *
     * @param array $dte Arreglo con los datos del XML (tag Documento)
     * @param string|null $timbre String XML con el tag TED del DTE, null para no dibujarlo
     */
    protected function addLetter(array $dte, $timbre = null)
    {
        $this->dte = $dte['Encabezado']['IdDoc']['TipoDTE'];
        // agregar página para la factura
        $this->AddPage();
        // agregar cabecera del documento
        $y[] = $this->agregarEmisor($dte['Encabezado']['Emisor']);
        $y[] = $this->agregarFolio(
            $dte['Encabezado']['Emisor']['RUTEmisor'],
            $dte['Encabezado']['IdDoc']['TipoDTE'],
            $dte['Encabezado']['IdDoc']['Folio'],
            !empty($dte['Encabezado']['Emisor']['CmnaOrigen']) ? $dte['Encabezado']['Emisor']['CmnaOrigen'] : null
        );
        $this->setY(max($y));
        $this->Ln();
        // datos del documento
        $y = [];
        $y[] = $this->agregarDatosEmision($dte['Encabezado']['IdDoc'], !empty($dte['Encabezado']['Emisor']['CdgVendedor'])?$dte['Encabezado']['Emisor']['CdgVendedor']:null);
        $y[] = $this->agregarReceptor($dte['Encabezado']);
        $this->setY(max($y));
        $this->agregarTraslado(
            !empty($dte['Encabezado']['IdDoc']['IndTraslado']) ? $dte['Encabezado']['IdDoc']['IndTraslado'] : null,
            !empty($dte['Encabezado']['Transporte']) ? $dte['Encabezado']['Transporte'] : null
        );
        if (!empty($dte['Referencia'])) {
            $this->agregarReferencia($dte['Referencia']);
        }
        $this->agregarDetalle($dte['Detalle']);
        if (!empty($dte['DscRcgGlobal'])) {
            $this->agregarSubTotal($dte['Detalle']);
            $this->agregarDescuentosRecargos($dte['DscRcgGlobal']);
        }
        if (!empty($dte['Encabezado']['IdDoc']['MntPagos'])) {
            $this->agregarPagos($dte['Encabezado']['IdDoc']['MntPagos']);
        }
        // si los datos invaden la zona del pie, el pie va en una página nueva
        $this->prepararPaginaPie($dte['Encabezado']['IdDoc']);
        // agregar observaciones
        $this->x_fin_datos = $this->getY();
        $this->agregarObservacion($dte['Encabezado']['IdDoc']);
        if (!$this->timbre_pie) {
            $this->Ln();
        }
        $this->x_fin_datos = $this->getY();
        $this->agregarTotales($dte['Encabezado']['Totales'], !empty($dte['Encabezado']['OtraMoneda']) ? $dte['Encabezado']['OtraMoneda'] : null);
        // agregar timbre
        $this->agregarTimbre($timbre);
        // agregar acuse de recibo y leyenda cedible
        if ($this->cedible and !in_array($dte['Encabezado']['IdDoc']['TipoDTE'], $this->sinAcuseRecibo)) {
            $this->agregarAcuseRecibo();
            $this->agregarLeyendaDestino($dte['Encabezado']['IdDoc']['TipoDTE']);
        }
    }

    /**
     * Método que agrega el detalle del documento. LibreDTE dibuja la tabla
     * desde $x, a la izquierda del margen de la página; cuando el detalle
     * salta de página, TCPDF repite el encabezado de la tabla en el margen y
     * queda corrido respecto de las filas. Para evitarlo, el margen izquierdo
     * se iguala a $x mientras se dibuja la tabla. Además ajusta el ancho de
     * las columnas numéricas al contenido de este documento.
     *
     * @param array $detalle Arreglo con el detalle del documento (tag Detalle del XML)
     * @param int|float $x Posición horizontal de inicio en el PDF
     * @param bool $html Si la tabla se genera en HTML
     */
    protected function agregarDetalle($detalle, $x = 10, $html = true)
    {
        $columnas = $this->detalle_cols;
        $this->ajustarAnchoColumnasDetalle($detalle);
        $margenIzquierdo = $this->lMargin;
        $this->SetLeftMargin($x);
        parent::agregarDetalle($detalle, $x, $html);
        $this->SetLeftMargin($margenIzquierdo);
        $this->detalle_cols = $columnas;
    }

    /**
     * Ensancha las columnas numéricas del detalle cuando algún valor no cabe
     * en su ancho por defecto (por ejemplo un precio unitario con decimales),
     * para que no se parta en dos renglones. Nunca las angosta, y el ancho
     * extra se descuenta de la columna "Item", que ocupa el espacio restante.
     *
     * @param array $detalle Arreglo con el detalle del documento (tag Detalle del XML)
     */
    protected function ajustarAnchoColumnasDetalle($detalle)
    {
        if (!is_array($detalle)) {
            return;
        }
        if (!isset($detalle[0])) {
            $detalle = [$detalle];
        }
        $margenes = $this->getCellPaddings();
        $relleno = $margenes['L'] + $margenes['R'] + self::DETALLE_COLUMNA_HOLGURA;
        foreach (self::DETALLE_COLUMNAS_AJUSTABLES as $columna) {
            if (!isset($this->detalle_cols[$columna]['width'])) {
                continue;
            }
            $ancho = (float)$this->detalle_cols[$columna]['width'];
            foreach ($detalle as $item) {
                if (!isset($item[$columna]) || !is_scalar($item[$columna]) || $item[$columna] === '') {
                    continue;
                }
                $texto = (string)$this->num($item[$columna]);
                $ancho = max($ancho, $this->GetStringWidth($texto, '', '', $this->detalle_fuente) + $relleno);
            }
            $this->detalle_cols[$columna]['width'] = min(ceil($ancho), self::DETALLE_COLUMNA_ANCHO_MAXIMO);
        }
    }

    /**
     * Método que agrega el timbre de la factura. Cuando no se conoce la
     * resolución del SII, replica agregarTimbre() de LibreDTE sin la línea
     * "Resolución N de AAAA", que de otro modo saldría vacía.
     *
     * @param string|null $timbre String con los datos del timbre
     * @param int|float $x_timbre Posición horizontal del código de barras
     * @param int|float $x Posición horizontal de los textos
     * @param int|float $y Posición vertical de inicio en el PDF
     * @param int|float $w Ancho del timbre
     * @param int|float $font_size Tamaño de la fuente de los textos
     * @param string|null $position Alineación del código de barras
     */
    protected function agregarTimbre($timbre, $x_timbre = 10, $x = 10, $y = 190, $w = 70, $font_size = 8, $position = null)
    {
        if ($timbre === null || $this->tieneResolucion()) {
            parent::agregarTimbre($timbre, $x_timbre, $x, $y, $w, $font_size, $position);

            return;
        }
        $y = (!$this->papelContinuo and !$this->timbre_pie) ? $this->x_fin_datos : $y;
        $style = [
            'border' => false,
            'padding' => 0,
            'hpadding' => 0,
            'vpadding' => 0,
            'module_width' => 1,
            'module_height' => 1,
            'fgcolor' => [0, 0, 0],
            'bgcolor' => false,
            'position' => $position === null ? ($this->papelContinuo ? 'C' : 'S') : $position,
        ];
        $this->write2DBarcode($timbre, 'PDF417,,' . $this->ecl, $x_timbre, $y, $w, 0, $style, 'B');
        $this->setFont('', 'B', $font_size);
        $this->Texto('Timbre Electrónico SII', $x, null, 'C', $w);
        $this->Ln();
        if ($w >= 60) {
            $this->Texto('Verifique documento: ' . $this->web_verificacion, $x, null, 'C', $w);
        } else {
            $this->Texto('Verifique documento:', $x, null, 'C', $w);
            $this->Ln();
            $this->Texto($this->web_verificacion, $x, null, 'C', $w);
        }
    }

    /**
     * Indica si se asignó la resolución del SII con número y fecha. El
     * número 0 es válido (ambiente de certificación).
     *
     * @return bool
     */
    protected function tieneResolucion(): bool
    {
        return is_array($this->resolucion)
            && isset($this->resolucion['NroResol'], $this->resolucion['FchResol'])
            && trim((string)$this->resolucion['NroResol']) !== ''
            && trim((string)$this->resolucion['FchResol']) !== '';
    }

    /**
     * Agrega una página nueva cuando los datos del documento terminan dentro
     * de la zona del pie, y registra la distribución resultante. Solo aplica
     * cuando el timbre va al pie (posiciones fijas); si el pie va pegado al
     * detalle no hay nada que pueda superponerse.
     *
     * @param array $IdDoc Tag IdDoc del documento
     */
    protected function prepararPaginaPie(array $IdDoc)
    {
        $yFinDatos = $this->getY();
        if (!$this->timbre_pie) {
            $this->distribucionPie = [
                'pagina'       => $this->getPage(),
                'y_fin_datos'  => $yFinDatos,
                'y_inicio_pie' => $yFinDatos,
                'salto'        => false,
            ];

            return;
        }
        $yInicioPie = self::PIE_Y;
        if ($this->tieneObservacion($IdDoc)) {
            $yInicioPie -= self::PIE_OBSERVACION_OFFSET;
        }
        $salto = $yFinDatos > $yInicioPie - self::PIE_MARGEN;
        if ($salto) {
            $this->AddPage();
            $yFinDatos = $this->getY();
        }
        $this->distribucionPie = [
            'pagina'       => $this->getPage(),
            'y_fin_datos'  => $yFinDatos,
            'y_inicio_pie' => $yInicioPie,
            'salto'        => $salto,
        ];
    }

    /**
     * Indica si el documento tiene datos que LibreDTE imprime como
     * observación sobre los totales (ver agregarObservacion() del padre)
     *
     * @param array $IdDoc Tag IdDoc del documento
     * @return bool
     */
    protected function tieneObservacion(array $IdDoc): bool
    {
        return !empty($IdDoc['TermPagoGlosa'])
            || !empty($IdDoc['MedioPago'])
            || !empty($IdDoc['TermPagoDias']);
    }
}
