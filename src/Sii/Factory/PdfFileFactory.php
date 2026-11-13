<?php
namespace HSDCL\DteCl\Sii\Factory;

use HSDCL\DteCl\Sii\Base\Pdf\FormatStrategy;
use HSDCL\DteCl\Sii\Base\Pdf\PdfDte;
use HSDCL\DteCl\Util\Exception;
use HSDCL\DteCl\Util\XmlEncoding;
use sasco\LibreDTE\Sii\EnvioDte;

/**
 * @version 16/2/21 3:25 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */
abstract class PdfFileFactory
{
    /**
     * Exportara los archivos a un directorio especifico
     *
     * @param string $filename
     * @param string $dirOutput
     * @param string $logoFileName
     * @throws Exception
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public static function make(FormatStrategy $strategy, string $filename, string $dirOutput)
    {
        # Read the XML
        $agent = new EnvioDte();
        $xml = XmlEncoding::normalize(file_get_contents($filename));
        $agent->loadXML($xml);
        # Si el XML es un DTE suelto, LibreDTE inventa una carátula (resolución
        # 9999 y fecha de hoy) que no debe llegar al PDF
        $caratula = self::isEnvelope($xml) ? $agent->getCaratula() : [];
        # Remove files
        $files = glob($dirOutput . '/*'); // get all file names
        foreach($files as $file){ // iterate files
            if(is_file($file)) {
                unlink($file); // delete file
            }
        }
        rmdir($dirOutput);
        if (!mkdir($dirOutput)) {
            throw new Exception("No se pudo crear el directorio temporal para DTEs");
        }
        # Iterate over documents
        foreach ($agent->getDocumentos() as $dte) {
            if (!$dte->getDatos()) {
                throw new Exception("No se pudo obtener los datos del DTE");
            }
            $strategy->build($dte, $dte->getEmisor(), ['caratula' => $caratula]);
        }

        return true;
    }

    /**
     * Indica si el XML es un sobre (EnvioDTE o EnvioBOLETA), que es el que
     * trae la carátula real del emisor
     *
     * @param string $xml
     * @return bool
     */
    private static function isEnvelope(string $xml): bool
    {
        $document = new \DOMDocument();
        if (!@$document->loadXML($xml) || !$document->documentElement) {
            return false;
        }

        return in_array($document->documentElement->localName, ['EnvioDTE', 'EnvioBOLETA'], true);
    }
}
