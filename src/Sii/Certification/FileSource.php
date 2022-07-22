<?php

namespace HSDCL\DteCl\Sii\Certification;

use HSDCL\DteCl\Sii\Base\Source;
use HSDCL\DteCl\Util\Configuration;
use sasco\LibreDTE\Sii\Certificacion\SetPruebas;

/**
 * Clase que leera de un archivo los casos
 * @version 5/8/20 7:05 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
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
     * @version 2020-08-05
     * @author  David Lopez <dleo.lopez@gmail.com>
     */
    public function getCases(array $folios = [], array $options = []): array
    {
        if (empty(
        $cases = json_decode(
            SetPruebas::getJSON(
                file_get_contents($this->config->getFilename()),
                $folios,
                $options['separator'] ?? "=============="
            ), true))) {
            return [];
        }

        return $this->sanatize($cases);
    }

    /**
     * @version
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function getInput()
    {
        return $this->config->getFilename();
    }

    /**
     * @param array $cases
     * @return array
     * @version 28/10/20
     * @author  David Lopez <dlopez@hsd.cl>
     */
    private function sanatize(array $cases): array
    {
        foreach ($cases as &$case) {
            if (isset($case['Referencia'][1]['RazonRef']) &&
                (strpos($case['Referencia'][1]['RazonRef'], 'CORRIGE GIRO') === 0 ||
                    strpos($case['Referencia'][1]['RazonRef'], 'ANULA NOTA DE CREDITO') === 0)) {
                $case['Detalle'][0]['NmbItem'] = 'CORRIGE DATO';
                continue;
            }

            foreach ($case['Detalle'] as $index => $details) {
                foreach ($details as $key => $detail) {
                    if (is_string($detail)) {
                        $details[$key] = mb_convert_encoding($detail, 'ISO-8859-1', 'UTF-8');
                    }
                }
                $case['Detalle'][$index] = $details;
            }
        }

        return $cases;
    }
}
