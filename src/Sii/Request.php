<?php
namespace HSDCL\DteCl\Sii;

use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Sii;
use sasco\LibreDTE\Sii\Autenticacion;
use sasco\LibreDTE\Sii\Token;

/**
 * Clase que manejara en los request a los servicios SII
 * @version 2/12/21 9:59 a. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */
class Request
{
    /**
     * @var FirmaElectronica
     */
    private $signature;

    /**
     * @var string|false|Token
     */
    protected $token;

    /**
     * @param string $filename URL del archivo
     * @param string $pass Pass de la firma
     * @throws Exception
     * @version 2/12/21
     * @author  David Lopez <dlopez@hsd.cl>
     */
    public function __construct(array $config)
    {
        $this->signature = new FirmaElectronica($config);
    }

    /**
     * Obtendra el token desde SII
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    private function getToken()
    {
        if (empty($this->token)) {
            # Probablemente necesitemos utilizar alguna forma de cache
            $this->token = Autenticacion::getToken($this->signature);
        }

        return $this->token;
    }

    /**
     * Enviar el dte
     * @version 2/12/21
     * @author  David Lopez <dlopez@hsd.cl>
     */
    public function sendDte(string $rutSender, string $rutEmitter, SimpleXMLElement $xml, int $enviroment = Sii::CERTIFICACION, bool $gzip = false, int $retry = null)
    {
        # Definir el ambiente, ¿como sera el comportamiento cuando varios esten accediendo
        # y cambiando el entorno
        Sii::setAmbiente($enviroment);

        return \sasco\LibreDTE\Sii::enviar($rutSender, $rutEmitter, $xml, $this->getToken(), $gzip, $retry);
    }
}
