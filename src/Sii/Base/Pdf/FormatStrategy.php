<?php
/**
 * @version 2/8/23 6:52 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Sii\Base\Pdf;

use sasco\LibreDTE\Sii\Dte;

/**
 * Class FormatStrategy
 * Formulara la estrategia para imprimir el DTE
 * @package HSDCL\DteCl\Sii\Base\Pdf
 * @author  David Lopez <dlopez@arisa.cl>
 */
abstract class FormatStrategy
{
    /**
     * @var PdfDte
     */
    protected $pdf;

    /**
     * @var string
     */
    protected $dirOuput;

    /**
     * @var array
     */
    protected $options;

    /**
     * Constructor
     *
     * Options puede terner
     * thermal_paper    Boolean         True Es papel continuo, False Tipo Carta
     * logo             String          Data de la imagen del logo
     * cedible          Boolean         True El documento es cedible, False No es cedible
     * without_timbre   Boolean         True El documento no lleva timbre, False Si lleva timbre fiscal
     * caratula         Array           Datos de la caratula
     * @version 2/8/23
     * @author David Lopez <dlopez@arisa.cl>>
     */
    public function __construct(string $dirOutput, array $options = [])
    {
        $this->dirOuput = $dirOutput;
        $this->options = $options;
    }

    /**
     * Metodod que creará el PDF del DTE
     * @return mixed
     * @author David Lopez <dlopez@arisa.cl>
     */
    abstract public function build(Dte $dte, string $fileName, array $extraInfo = []);

    /**
     * Obtiene la resolución del SII (número y fecha) desde la carátula del
     * sobre que entrega la fábrica en $extraInfo['caratula'].
     *
     * Retorna un arreglo vacío cuando no hay carátula o le falta alguno de
     * los dos datos. El número 0 es válido: es el que exige el SII en el
     * ambiente de certificación.
     *
     * @param array $extraInfo
     * @return array Arreglo con FchResol y NroResol, o vacío si no hay resolución
     */
    protected function getResolucion(array $extraInfo): array
    {
        $caratula = $extraInfo['caratula'] ?? null;
        if (!is_array($caratula)) {
            return [];
        }
        $numero = $caratula['NroResol'] ?? '';
        $fecha = $caratula['FchResol'] ?? '';
        if (!is_scalar($numero) || !is_scalar($fecha) || trim((string)$numero) === '' || trim((string)$fecha) === '') {
            return [];
        }

        return [
            'FchResol' => trim((string)$fecha),
            'NroResol' => trim((string)$numero),
        ];
    }
}
