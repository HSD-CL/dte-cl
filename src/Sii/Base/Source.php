<?php
namespace HSDCL\DteCl\Sii\Base;

/**
 * Interface Fuente
 * Representa una fuente de datos para la certificacion
 * @author David Lopez <dleo.lopez@gmail.com>
 * @version 5/8/20 6:36 p. m.
 */
interface Source
{
    /**
     * Obtendra los casos y los retorna como un array para su procesamiento
     * @throw Exception
     * @param array $folios
     * @return array
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function getCases(array $folios = []): array;

    /**
     * @return mixed
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function getInput();
}
