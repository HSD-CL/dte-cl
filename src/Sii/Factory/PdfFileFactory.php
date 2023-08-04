<?php
namespace HSDCL\DteCl\Sii\Factory;

use HSDCL\DteCl\Sii\Base\Pdf\FormatStrategy;
use HSDCL\DteCl\Sii\Base\Pdf\PdfDte;
use HSDCL\DteCl\Util\Exception;
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
        $agent->loadXML(file_get_contents($filename));
        $caratula = $agent->getCaratula();
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
}
