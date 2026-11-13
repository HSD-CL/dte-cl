<?php

namespace HSDCL\DteCl\Tests\Feature;

use HSDCL\DteCl\Sii\Base\Pdf\FormatStrategy;
use HSDCL\DteCl\Sii\Base\Pdf\LetterFormatStrategy;
use HSDCL\DteCl\Sii\Base\Pdf\PdfDte;
use HSDCL\DteCl\Sii\Factory\PdfFileFactory;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use sasco\LibreDTE\Sii\Dte;

/**
 * Verifica que la resolución del SII que se imprime bajo el timbre salga de
 * la carátula real del sobre, y que la línea se omita cuando no se conoce.
 *
 * No usa Testbench: registra solo la configuración que necesita PdfDte.
 *
 * @author Danilo Vásquez
 */
class PdfResolucionTest extends TestCase
{
    /**
     * @var string
     */
    private $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dir = sys_get_temp_dir() . '/dte-cl-resolucion-' . uniqid();
        mkdir($this->dir);
        mkdir($this->dir . '/out');
        Container::getInstance()->instance('config', new Repository(['dte-cl' => ['pdf' => [
            'title' => 'Test', 'author' => 'Test', 'creator' => 'Test', 'footer' => 'Test',
        ]]]));
    }

    protected function tearDown(): void
    {
        Container::setInstance(null);
        foreach (array_merge(glob($this->dir . '/out/*') ?: [], glob($this->dir . '/*.xml') ?: []) as $file) {
            unlink($file);
        }
        @rmdir($this->dir . '/out');
        @rmdir($this->dir);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function laFabricaEntregaLaCaratulaRealDelSobre()
    {
        $caratula = $this->caratulaQueRecibeLaEstrategia($this->makeEnvio('80', '2014-08-22'));

        $this->assertSame('80', (string)$caratula['NroResol']);
        $this->assertSame('2014-08-22', $caratula['FchResol']);
    }

    /**
     * Un DTE suelto no trae carátula: LibreDTE inventa una (resolución 9999 y
     * fecha de hoy) que no debe llegar a la estrategia
     *
     * @test
     */
    public function laFabricaNoEntregaLaCaratulaInventadaDeUnDteSuelto()
    {
        $this->assertSame([], $this->caratulaQueRecibeLaEstrategia($this->makeDte()));
    }

    /**
     * @test
     */
    public function elPdfCartaMuestraLaResolucionDelSobre()
    {
        $texto = $this->textoDelPdfCarta($this->makeEnvio('80', '2014-08-22'));

        $this->assertStringContainsString('Resolución 80 de 2014', $texto);
    }

    /**
     * En certificación el SII exige NroResol = 0 y esa carátula es legítima
     *
     * @test
     */
    public function elPdfCartaMuestraLaResolucionCeroDeCertificacion()
    {
        $texto = $this->textoDelPdfCarta($this->makeEnvio('0', '2014-08-22'));

        $this->assertStringContainsString('Resolución 0 de 2014', $texto);
    }

    /**
     * @test
     */
    public function elPdfCartaOmiteLaLineaSiNoHayResolucion()
    {
        $texto = $this->textoDelPdfCarta($this->makeDte());

        $this->assertStringContainsString('Timbre Electrónico SII', $texto);
        $this->assertStringContainsString('Verifique documento', $texto);
        $this->assertStringNotContainsString('Resolución', $texto);
    }

    /**
     * @test
     */
    public function elPdfCartaOmiteLaLineaSiLaCaratulaVieneSinResolucion()
    {
        $texto = $this->textoDelPdfCarta($this->makeEnvio('', ''));

        $this->assertStringContainsString('Timbre Electrónico SII', $texto);
        $this->assertStringNotContainsString('Resolución', $texto);
    }

    /**
     * @test
     */
    public function pdfDteOmiteLaLineaEnPapelContinuoSinResolucion()
    {
        $datos = (new Dte($this->makeDte(), false))->getDatos();
        $pdf = new PdfDte(80, ['title' => 'T', 'author' => 'T', 'creator' => 'T', 'footer' => 'T']);
        $pdf->addContinuousPaper80($datos, '<TED>prueba</TED>');
        $texto = $this->textoDe($pdf->Output('', 'S'));

        $this->assertStringContainsString('Timbre Electrónico SII', $texto);
        $this->assertStringNotContainsString('Resolución', $texto);
    }

    /**
     * Ejecuta la fábrica con una estrategia espía y retorna la carátula que
     * recibió
     */
    private function caratulaQueRecibeLaEstrategia(string $xml)
    {
        $spy = new class($this->dir . '/out') extends FormatStrategy {
            public $extraInfo;

            public function build(Dte $dte, string $fileName, array $extraInfo = [])
            {
                $this->extraInfo = $extraInfo;
            }
        };
        file_put_contents($this->dir . '/dte.xml', $xml);
        PdfFileFactory::make($spy, $this->dir . '/dte.xml', $this->dir . '/out');

        return $spy->extraInfo['caratula'];
    }

    /**
     * Genera el PDF carta con la fábrica y la estrategia reales, y retorna el
     * texto que contiene
     */
    private function textoDelPdfCarta(string $xml): string
    {
        $strategy = new LetterFormatStrategy($this->dir . '/out', ['cedible' => false, 'without_timbre' => false]);
        file_put_contents($this->dir . '/dte.xml', $xml);
        PdfFileFactory::make($strategy, $this->dir . '/dte.xml', $this->dir . '/out');

        $archivos = glob($this->dir . '/out/*.pdf');
        $this->assertCount(1, $archivos);

        return $this->textoDe(file_get_contents($archivos[0]));
    }

    /**
     * Extrae el texto de un PDF generado por TCPDF con fuentes
     * estándar: cada texto va como cadena en Latin-1 entre paréntesis, antes
     * del operador Tj o dentro de un arreglo TJ.
     */
    private function textoDe(string $pdf): string
    {
        // los flujos de contenido pueden venir comprimidos
        $pdf = preg_replace_callback('/stream\r?\n(.*?)\r?\nendstream/s', function ($m) {
            $plano = @gzuncompress($m[1]);

            return $plano === false ? $m[0] : $plano;
        }, $pdf);
        $cadena = '\\((?:\\\\.|[^\\\\()])*\\)';
        preg_match_all('/(' . $cadena . ')\s*Tj|\[((?:' . $cadena . '|[^\]()])*)\]\s*TJ/s', $pdf, $matches, PREG_SET_ORDER);
        $texto = [];
        foreach ($matches as $match) {
            preg_match_all('/' . $cadena . '/s', $match[0], $partes);
            $linea = '';
            foreach ($partes[0] as $parte) {
                $linea .= preg_replace_callback('/\\\\([0-7]{1,3}|.)/s', function ($m) {
                    return ctype_digit($m[1]) ? chr(octdec($m[1])) : $m[1];
                }, substr($parte, 1, -1));
            }
            $texto[] = mb_convert_encoding($linea, 'UTF-8', 'ISO-8859-1');
        }

        return implode("\n", $texto);
    }

    private function makeDte(): string
    {
        return '<?xml version="1.0" encoding="ISO-8859-1"?>' . "\n" . $this->makeDteBody();
    }

    private function makeEnvio(string $nroResol, string $fchResol): string
    {
        return '<?xml version="1.0" encoding="ISO-8859-1"?>' . "\n"
            . '<EnvioDTE xmlns="http://www.sii.cl/SiiDte" version="1.0"><SetDTE ID="SetDoc">'
            . '<Caratula version="1.0"><RutEmisor>76192083-9</RutEmisor><RutEnvia>11222333-4</RutEnvia>'
            . '<RutReceptor>60803000-K</RutReceptor>'
            . '<FchResol>' . $fchResol . '</FchResol><NroResol>' . $nroResol . '</NroResol>'
            . '<TmstFirmaEnv>2026-11-06T10:00:00</TmstFirmaEnv>'
            . '<SubTotDTE><TpoDTE>33</TpoDTE><NroDTE>1</NroDTE></SubTotDTE></Caratula>'
            . $this->makeDteBody()
            . '</SetDTE></EnvioDTE>';
    }

    private function makeDteBody(): string
    {
        return '<DTE version="1.0"><Documento ID="T33F1234"><Encabezado>'
            . '<IdDoc><TipoDTE>33</TipoDTE><Folio>1234</Folio><FchEmis>2026-11-06</FchEmis></IdDoc>'
            . '<Emisor><RUTEmisor>76192083-9</RUTEmisor><RznSoc>Emisor de Prueba S.A</RznSoc>'
            . '<GiroEmis>VENTA AL POR MAYOR</GiroEmis><Acteco>463020</Acteco>'
            . '<DirOrigen>Camino Alto 1031</DirOrigen><CmnaOrigen>Coquimbo</CmnaOrigen></Emisor>'
            . '<Receptor><RUTRecep>60803000-K</RUTRecep><RznSocRecep>RECEPTOR DE PRUEBA SPA</RznSocRecep>'
            . '<GiroRecep>ABARROTES</GiroRecep><DirRecep>Calle de Prueba 1641</DirRecep>'
            . '<CmnaRecep>COQUIMBO</CmnaRecep></Receptor>'
            . '<Totales><MntNeto>1000</MntNeto><TasaIVA>19</TasaIVA><IVA>190</IVA><MntTotal>1190</MntTotal></Totales>'
            . '</Encabezado>'
            . '<Detalle><NroLinDet>1</NroLinDet><NmbItem>Producto de prueba</NmbItem>'
            . '<QtyItem>1</QtyItem><PrcItem>1000</PrcItem><MontoItem>1000</MontoItem></Detalle>'
            . '</Documento></DTE>';
    }
}
