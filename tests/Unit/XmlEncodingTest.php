<?php

namespace HSDCL\DteCl\Tests\Unit;

use HSDCL\DteCl\Sii\Base\Pdf\PdfDte;
use HSDCL\DteCl\Util\XmlEncoding;
use PHPUnit\Framework\TestCase;
use sasco\LibreDTE\Sii\Dte;

/**
 * Verifica la normalización de la codificación de los XML antes de generar
 * el PDF. No necesita el contenedor de Laravel.
 *
 * @author Danilo Vásquez
 */
class XmlEncodingTest extends TestCase
{
    /**
     * "ñ" codificada en UTF-8 y en ISO-8859-1
     */
    const ENIE_UTF8 = "\xC3\xB1";
    const ENIE_ISO = "\xF1";

    /**
     * @test
     */
    public function convierteUtf8DeclaradoComoIso88591()
    {
        $xml = $this->makeXml('ISO-8859-1', self::ENIE_UTF8);

        $this->assertSame($this->makeXml('ISO-8859-1', self::ENIE_ISO), XmlEncoding::normalize($xml));
    }

    /**
     * @test
     */
    public function noAlteraIso88591Real()
    {
        $xml = $this->makeXml('ISO-8859-1', self::ENIE_ISO);

        $this->assertSame($xml, XmlEncoding::normalize($xml));
    }

    /**
     * @test
     */
    public function noAlteraUtf8DeclaradoComoUtf8()
    {
        $xml = $this->makeXml('UTF-8', self::ENIE_UTF8);

        $this->assertSame($xml, XmlEncoding::normalize($xml));
    }

    /**
     * @test
     */
    public function noAlteraContenidoSoloAscii()
    {
        $xml = $this->makeXml('ISO-8859-1', 'n');

        $this->assertSame($xml, XmlEncoding::normalize($xml));
    }

    /**
     * @test
     */
    public function noAlteraXmlSinDeclaracion()
    {
        $xml = '<DTE><DirOrigen>Pe' . self::ENIE_UTF8 . 'uelas</DirOrigen></DTE>';

        $this->assertSame($xml, XmlEncoding::normalize($xml));
    }

    /**
     * @test
     */
    public function aceptaDeclaracionEnMinusculasYComillasSimples()
    {
        $xml = "<?xml version='1.0' encoding='iso-8859-1'?><DTE>Pe" . self::ENIE_UTF8 . 'uelas</DTE>';

        $this->assertSame(
            "<?xml version='1.0' encoding='iso-8859-1'?><DTE>Pe" . self::ENIE_ISO . 'uelas</DTE>',
            XmlEncoding::normalize($xml)
        );
    }

    /**
     * Un carácter que no existe en ISO-8859-1 (comilla tipográfica) no se
     * puede convertir sin perderlo: se conserva UTF-8 y se corrige la cabecera
     *
     * @test
     */
    public function conservaUtf8SiHayCaracteresFueraDeIso88591()
    {
        $comilla = "\xE2\x80\x9C";
        $xml = $this->makeXml('ISO-8859-1', self::ENIE_UTF8 . $comilla);

        $this->assertSame($this->makeXml('UTF-8', self::ENIE_UTF8 . $comilla), XmlEncoding::normalize($xml));
    }

    /**
     * @test
     */
    public function quitaElBomAlConvertir()
    {
        $xml = XmlEncoding::BOM_UTF8 . $this->makeXml('ISO-8859-1', self::ENIE_UTF8);

        $this->assertSame($this->makeXml('ISO-8859-1', self::ENIE_ISO), XmlEncoding::normalize($xml));
    }

    /**
     * Sin normalizar, LibreDTE entrega la dirección dañada; normalizado, la
     * entrega bien. Además genera el PDF de ejemplo.
     *
     * @test
     */
    public function libreDteLeeLaDireccionCorrectaTrasNormalizar()
    {
        $xml = $this->makeXml('ISO-8859-1', self::ENIE_UTF8);

        $danado = (new Dte($xml, false))->getDatos();
        $this->assertSame('Camino Alto PeÃ±uelas / 1031', $danado['Encabezado']['Emisor']['DirOrigen']);

        $datos = (new Dte(XmlEncoding::normalize($xml), false))->getDatos();
        $this->assertSame('Camino Alto Peñuelas / 1031', $datos['Encabezado']['Emisor']['DirOrigen']);

        $pdf = new PdfDte(false, ['title' => 'Test', 'author' => 'Test', 'creator' => 'Test', 'footer' => 'Test']);
        $pdf->agregar($datos);
        $dir = __DIR__ . '/../../build/pdf-samples';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $pdf->Output($dir . '/carta_codificacion_enie.pdf', 'F');
        $this->assertFileExists($dir . '/carta_codificacion_enie.pdf');
    }

    /**
     * También un XML realmente codificado en ISO-8859-1 se sigue leyendo bien
     *
     * @test
     */
    public function libreDteLeeIso88591RealSinCambios()
    {
        $xml = XmlEncoding::normalize($this->makeXml('ISO-8859-1', self::ENIE_ISO));

        $datos = (new Dte($xml, false))->getDatos();
        $this->assertSame('Camino Alto Peñuelas / 1031', $datos['Encabezado']['Emisor']['DirOrigen']);
    }

    /**
     * Arma un DTE mínimo con la "ñ" de la dirección en los bytes indicados
     *
     * @param string $encoding Codificación declarada en la cabecera
     * @param string $enie Bytes con que se escribe la "ñ"
     * @return string
     */
    private function makeXml(string $encoding, string $enie): string
    {
        return '<?xml version="1.0" encoding="' . $encoding . '"?>' . "\n"
            . '<DTE version="1.0"><Documento ID="T33F1234"><Encabezado>'
            . '<IdDoc><TipoDTE>33</TipoDTE><Folio>1234</Folio><FchEmis>2026-11-06</FchEmis></IdDoc>'
            . '<Emisor><RUTEmisor>76192083-9</RUTEmisor><RznSoc>Emisor de Prueba S.A</RznSoc>'
            . '<GiroEmis>VENTA AL POR MAYOR</GiroEmis><Acteco>463020</Acteco>'
            . '<DirOrigen>Camino Alto Pe' . $enie . 'uelas / 1031</DirOrigen>'
            . '<CmnaOrigen>Coquimbo</CmnaOrigen><CiudadOrigen>Coquimbo</CiudadOrigen></Emisor>'
            . '<Receptor><RUTRecep>60803000-K</RUTRecep><RznSocRecep>RECEPTOR DE PRUEBA SPA</RznSocRecep>'
            . '<GiroRecep>ABARROTES</GiroRecep><DirRecep>Calle de Prueba 1641</DirRecep>'
            . '<CmnaRecep>COQUIMBO</CmnaRecep></Receptor>'
            . '<Totales><MntNeto>52030</MntNeto><TasaIVA>19</TasaIVA><IVA>9886</IVA><MntTotal>61916</MntTotal></Totales>'
            . '</Encabezado>'
            . '<Detalle><NroLinDet>1</NroLinDet><NmbItem>Producto de prueba</NmbItem>'
            . '<QtyItem>1</QtyItem><PrcItem>52030</PrcItem><MontoItem>52030</MontoItem></Detalle>'
            . '</Documento></DTE>';
    }
}
