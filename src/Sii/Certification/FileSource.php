<?php
namespace HSDCL\DteCl\Sii\Certification;

use HSDCL\DteCl\Util\Configuration;
use sasco\LibreDTE\Sii\Certificacion\SetPruebas;

/**
 * Clase que leera de un archivo los casos
 * @author David Lopez <dleo.lopez@gmail.com>
 * @version 5/8/20 7:05 p. m.
 */
class FileSource implements Source
{
    /**
     * @var Configuration
     */
    private $config;
    /**
     * FileSource constructor.
     * @param string $filename
     * @throws \HSDCL\DteCl\Util\Exception
     */
    public function __construct(string $filename)
    {
        $this->config = Configuration::getInstance('cert-file-source', $filename);
    }

    /**
     * Obtendra los casos desde un archivo
     * @param array $folios
     * @return array
     * @author David Lopez <dleo.lopez@gmail.com>
     * @version 2020-08-05
     */
    public function getCases(array $folios = []): array
    {
        if (empty($cases = json_decode(SetPruebas::getJSON(file_get_contents($this->config->getFilename()), $folios), true))) {
            return [];
        }

        return $cases;
    }

}
