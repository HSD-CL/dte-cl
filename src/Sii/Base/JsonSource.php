<?php
/**
 * @version 3/2/21 5:31 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Sii\Base;


/**
 * Class JsonSource
 *
 * Clase que sirve para ser la fuente de los Dte desde un JSON
 * @version 202207211450
 * @author  David Lopez <dleo.lopez@gmail.com>
 * @package HSDCL\DteCl\Sii\Base
 */
class JsonSource implements Source
{
    /**
     * @var array DTE
     */
    protected array $cases;

    /**
     * JsonSource constructor.
     * @param string $cases
     */
    public function __construct(string $cases)
    {
        $decodeCases = json_decode($cases, true);
        if (array_keys($decodeCases) !== range(0, count($decodeCases) - 1)) {
            # Solo hay un DTE, validamos la forma
            $this->cases[] = (new DteStructure($decodeCases))->getArrayCopy();

            return;
        }
        # Si hay más de un caso debemos validar caso por caso
        foreach ($decodeCases as $case) {
            $this->cases[] = (new DteStructure($case))->getArrayCopy();
        }
    }

    /**
     * Desde el string crear los casos
     * @param array $folios
     * @return array
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function getCases(array $folios = [], array $options = []): array
    {
        return $this->cases;
    }

    /**
     * @return mixed
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function getInput()
    {
        return $this->cases;
    }
}
