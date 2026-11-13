<?php

namespace HSDCL\DteCl\Tests\Feature;

use HSDCL\DteCl\Sii\Base\Pdf\PdfDte;
use PHPUnit\Framework\TestCase;

/**
 * Verifica que en papel carta el pie del documento (observación, totales,
 * timbre y acuse de recibo) no quede sobre el detalle. No necesita el
 * contenedor de Laravel: los metadatos del PDF se pasan por opciones.
 *
 * Cada caso deja su PDF en build/pdf-samples/ para la revisión visual.
 *
 * @author Danilo Vásquez
 */
class PdfDteLetterLayoutTest extends TestCase
{
    /**
     * @test
     * @dataProvider lineasYPaginas
     */
    public function elPieNoSeSuperponeAlDetalle(int $lineas, int $esperadas, bool $salto)
    {
        $pdf = $this->makePdf();
        $pdf->agregar($this->makeDte($lineas), $this->makeTimbre());
        [$paginas, $pie] = $this->medirYGuardar($pdf, "carta_{$lineas}_lineas");

        $this->assertSame($esperadas, $paginas, 'Número de páginas');
        $this->assertSame($esperadas, $pie['pagina'], 'El pie va en la última página');
        $this->assertSame($salto, $pie['salto'], 'Página adicional solo para el pie');
        $this->assertSame(PdfDte::PIE_Y, $pie['y_inicio_pie']);
        $this->assertPieDespuesDeDatos($pie);
    }

    public static function lineasYPaginas(): array
    {
        return [
            '10 líneas: una página'                             => [10, 1, false],
            '30 líneas: el pie pasa a la página 2'              => [30, 2, true],
            '40 líneas: llena la página 1 y el pie pasa a la 2' => [40, 2, true],
            '45 líneas: el detalle sigue en la 2 y el pie cabe' => [45, 2, false],
            '60 líneas: el detalle sigue en la 2 y el pie cabe' => [60, 2, false],
            '80 líneas: el pie pasa a la página 3'              => [80, 3, true],
            '150 líneas: cuatro páginas y el pie cabe en la 4'  => [150, 4, false],
        ];
    }

    /**
     * 24 líneas es el caso límite: según el alto de la cabecera puede caber
     * en una página o necesitar dos, pero nunca debe superponerse.
     *
     * @test
     */
    public function elCasoLimiteDe24LineasNoSeSuperpone()
    {
        $pdf = $this->makePdf();
        $pdf->agregar($this->makeDte(24), $this->makeTimbre());
        [$paginas, $pie] = $this->medirYGuardar($pdf, 'carta_24_lineas');

        $this->assertSame($paginas, $pie['pagina']);
        $this->assertSame($pie['salto'] ? 2 : 1, $paginas);
        $this->assertPieDespuesDeDatos($pie);
    }

    /**
     * La observación de pago se dibuja 15 mm sobre los totales. Un detalle
     * que cabe sin observación debe pasar el pie a otra página cuando la hay.
     *
     * @test
     */
    public function laObservacionAdelantaElInicioDelPie()
    {
        $lineas = $this->lineasQueTerminanEnZonaDeObservacion();

        $sinObservacion = $this->makePdf();
        $sinObservacion->agregar($this->makeDte($lineas), $this->makeTimbre());
        [$paginas, $pie] = $this->medirYGuardar($sinObservacion, "carta_{$lineas}_lineas_sin_observacion");
        $this->assertSame(1, $paginas);
        $this->assertFalse($pie['salto']);

        $conObservacion = $this->makePdf();
        $conObservacion->agregar($this->makeDte($lineas, true), $this->makeTimbre());
        [$paginas, $pie] = $this->medirYGuardar($conObservacion, "carta_{$lineas}_lineas_con_observacion");
        $this->assertSame(2, $paginas);
        $this->assertTrue($pie['salto']);
        $this->assertSame(PdfDte::PIE_Y - PdfDte::PIE_OBSERVACION_OFFSET, $pie['y_inicio_pie']);
        $this->assertPieDespuesDeDatos($pie);
    }

    /**
     * @test
     */
    public function sinTimbreAplicaLaMismaPaginacion()
    {
        $pdf = $this->makePdf();
        $pdf->agregar($this->makeDte(30), null);
        [$paginas, $pie] = $this->medirYGuardar($pdf, 'carta_30_lineas_sin_timbre');

        $this->assertSame(2, $paginas);
        $this->assertTrue($pie['salto']);
        $this->assertPieDespuesDeDatos($pie);
    }

    /**
     * @test
     */
    public function addWithoutTimbreUsaElMismoFlujo()
    {
        $pdf = $this->makePdf();
        $pdf->addWithoutTimbre($this->makeDte(30));
        [$paginas, $pie] = $this->medirYGuardar($pdf);

        $this->assertSame(2, $paginas);
        $this->assertTrue($pie['salto']);
        $this->assertPieDespuesDeDatos($pie);
    }

    /**
     * @test
     */
    public function cedibleConAcuseDeReciboTampocoSeSuperpone()
    {
        $pdf = $this->makePdf();
        $pdf->setCedible(true);
        $pdf->agregar($this->makeDte(30), $this->makeTimbre());
        [$paginas, $pie] = $this->medirYGuardar($pdf, 'carta_30_lineas_cedible');

        $this->assertSame(2, $paginas);
        $this->assertPieDespuesDeDatos($pie);
    }

    /**
     * Con el pie pegado al detalle (sin posiciones fijas) no se fuerza
     * ninguna página adicional
     *
     * @test
     */
    public function conElPiePegadoAlDetalleNoSeFuerzaElSalto()
    {
        $pdf = $this->makePdf();
        $pdf->setTimbrePie(false);
        $pdf->agregar($this->makeDte(10), $this->makeTimbre());
        [$paginas, $pie] = $this->medirYGuardar($pdf);

        $this->assertSame(1, $paginas);
        $this->assertFalse($pie['salto']);
        $this->assertSame($pie['y_fin_datos'], $pie['y_inicio_pie']);
    }

    /**
     * El papel continuo sigue pasando por LibreDTE: una sola página y sin
     * distribución de pie registrada
     *
     * @test
     */
    public function elPapelContinuoNoCambia()
    {
        $pdf = new PdfDte(80, $this->options());
        $pdf->setResolucion(['FchResol' => '2014-08-22', 'NroResol' => 80]);
        $pdf->agregar($this->makeDte(30), $this->makeTimbre());
        [$paginas, $pie] = $this->medirYGuardar($pdf);

        $this->assertSame(1, $paginas);
        $this->assertNull($pie);
    }

    /**
     * Comprueba que en la página del pie los datos terminan antes de que
     * empiece el pie, con la separación mínima
     */
    private function assertPieDespuesDeDatos(array $pie): void
    {
        $this->assertLessThanOrEqual(
            $pie['y_inicio_pie'] - PdfDte::PIE_MARGEN,
            $pie['y_fin_datos'],
            "Los datos terminan en {$pie['y_fin_datos']} mm y el pie empieza en {$pie['y_inicio_pie']} mm"
        );
    }

    /**
     * Busca una cantidad de líneas cuyo detalle termine entre el inicio de la
     * observación y el inicio de los totales
     */
    private function lineasQueTerminanEnZonaDeObservacion(): int
    {
        $desde = PdfDte::PIE_Y - PdfDte::PIE_OBSERVACION_OFFSET;
        $hasta = PdfDte::PIE_Y - PdfDte::PIE_MARGEN;
        for ($lineas = 10; $lineas <= 40; $lineas++) {
            $pdf = $this->makePdf();
            $pdf->agregar($this->makeDte($lineas), $this->makeTimbre());
            [$paginas, $pie] = $this->medirYGuardar($pdf);
            if ($paginas === 1 && $pie['y_fin_datos'] > $desde && $pie['y_fin_datos'] <= $hasta) {
                return $lineas;
            }
        }
        $this->fail('No se encontró un detalle que termine en la zona de la observación');
    }

    private function makePdf(): PdfDte
    {
        $pdf = new PdfDte(false, $this->options());
        $pdf->setResolucion(['FchResol' => '2014-08-22', 'NroResol' => 80]);

        return $pdf;
    }

    private function options(): array
    {
        return ['title' => 'Test', 'author' => 'Test', 'creator' => 'Test', 'footer' => 'Test - https://example.com'];
    }

    /**
     * Arma los datos de una factura con la cantidad de líneas indicada
     */
    private function makeDte(int $lineas, bool $conObservacion = false): array
    {
        $detalle = [];
        for ($i = 1; $i <= $lineas; $i++) {
            $detalle[] = [
                'NroLinDet' => $i,
                'CdgItem'   => ['TpoCodigo' => 'INT', 'VlrCodigo' => (string)(10200000 + $i)],
                'NmbItem'   => "Producto de prueba número {$i}",
                'QtyItem'   => 1,
                'PrcItem'   => 1000,
                'MontoItem' => 1000,
            ];
        }
        $neto = $lineas * 1000;
        $iva = (int)round($neto * 0.19);
        $idDoc = [
            'TipoDTE' => 33,
            'Folio'   => 1234,
            'FchEmis' => '2026-11-06',
            'FmaPago' => 1,
            'FchVenc' => '2026-11-06',
        ];
        if ($conObservacion) {
            $idDoc['TermPagoGlosa'] = 'Pago contra entrega';
            $idDoc['MedioPago'] = 'EF';
        }

        return [
            'Encabezado' => [
                'IdDoc'    => $idDoc,
                'Emisor'   => [
                    'RUTEmisor'    => '76192083-9',
                    'RznSoc'       => 'Emisor de Prueba S.A',
                    'GiroEmis'     => 'VENTA AL POR MAYOR',
                    'DirOrigen'    => 'Camino Alto Peñuelas / 1031',
                    'CmnaOrigen'   => 'Coquimbo',
                    'CiudadOrigen' => 'Coquimbo',
                ],
                'Receptor' => [
                    'RUTRecep'    => '60803000-K',
                    'RznSocRecep' => 'RECEPTOR DE PRUEBA SPA',
                    'GiroRecep'   => 'ABARROTES',
                    'DirRecep'    => 'Calle de Prueba 1641',
                    'CmnaRecep'   => 'COQUIMBO',
                ],
                'Totales'  => [
                    'MntNeto'  => $neto,
                    'MntExe'   => 0,
                    'TasaIVA'  => 19,
                    'IVA'      => $iva,
                    'MntTotal' => $neto + $iva,
                ],
            ],
            'Detalle'    => $detalle,
            'Referencia' => [
                'NroLinRef' => 1,
                'TpoDocRef' => 801,
                'FolioRef'  => 'OC-00001',
                'FchRef'    => '2026-11-05',
            ],
        ];
    }

    /**
     * Texto con el largo aproximado de un TED real; el código PDF417 no
     * valida su contenido
     */
    private function makeTimbre(): string
    {
        return '<TED version="1.0"><DD><RE>76192083-9</RE><TD>33</TD><F>1234</F><FE>2026-11-06</FE>'
            . '<RR>60803000-K</RR><RSR>RECEPTOR DE PRUEBA SPA</RSR><MNT>35700</MNT><IT1>Producto de prueba</IT1>'
            . '<CAF version="1.0"><DA><RE>76192083-9</RE><RS>EMISOR DE PRUEBA</RS><TD>33</TD>'
            . '<RNG><D>17000000</D><H>18000000</H></RNG><FA>2026-01-01</FA>'
            . '<RSAPK><M>' . str_repeat('A', 88) . '</M><E>Aw==</E></RSAPK><IDK>100</IDK></DA>'
            . '<FRMA algoritmo="SHA1withRSA">' . str_repeat('B', 88) . '</FRMA></CAF>'
            . '<TSTED>2026-11-06T10:00:00</TSTED></DD>'
            . '<FRMT algoritmo="SHA1withRSA">' . str_repeat('C', 88) . '</FRMT></TED>';
    }

    /**
     * Lee el número de páginas y la distribución del pie y, si se indica un
     * nombre, guarda el PDF de ejemplo. Las medidas se leen antes de guardar
     * porque Output() libera el estado de TCPDF.
     *
     * @return array [páginas, distribución del pie]
     */
    private function medirYGuardar(PdfDte $pdf, ?string $nombre = null): array
    {
        $medidas = [$pdf->getNumPages(), $pdf->getDistribucionPie()];
        if ($nombre !== null) {
            $dir = __DIR__ . '/../../build/pdf-samples';
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $pdf->Output($dir . "/{$nombre}.pdf", 'F');
            $this->assertFileExists($dir . "/{$nombre}.pdf");
        }

        return $medidas;
    }
}
