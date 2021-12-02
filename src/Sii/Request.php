<?php

use sasco\LibreDTE\FirmaElectronica;
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
    private FirmaElectronica $signature;

    /**
     * @var string|false|Token
     */
    protected string|false|Token $token;

    /**
     * @param string $filename URL del archivo
     * @param string $pass Pass de la firma
     * @throws Exception
     * @version 2/12/21
     * @author  David Lopez <dlopez@hsd.cl>
     */
    public function __construct(string $filename, string $pass)
    {
        if (!file_exists($filename)) {
            throw new Exception("File doesn't exist");
        }
        $this->signature = new FirmaElectronica(['file' => $filename, 'pass' => $pass]);
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
    public function sendDte(string $rutSender, string $rutEmitter, SimpleXMLElement $xml, bool $gzip = false, int $retry = null): SimpleXMLElement|bool|\sasco\LibreDTE\Respuesta
    {
        return \sasco\LibreDTE\Sii::enviar($rutSender, $rutEmitter, $xml, $this->getToken(), $gzip, $retry);
    }
}
