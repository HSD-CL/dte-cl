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
     * se iguala a $x mientras se dibuja la tabla.
     *
     * @param array $detalle Arreglo con el detalle del documento (tag Detalle del XML)
     * @param int|float $x Posición horizontal de inicio en el PDF
     * @param bool $html Si la tabla se genera en HTML
     */
    protected function agregarDetalle($detalle, $x = 10, $html = true)
    {
        $margenIzquierdo = $this->lMargin;
        $this->SetLeftMargin($x);
        parent::agregarDetalle($detalle, $x, $html);
        $this->SetLeftMargin($margenIzquierdo);
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
