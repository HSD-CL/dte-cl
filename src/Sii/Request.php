<?php
namespace HSDCL\DteCl\Sii;

use HSDCL\DteCl\Util\Arr;
use HSDCL\DteCl\Util\Exception;
use Illuminate\Support\Facades\Cache;
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
    const STATUS_UPLOAD_OK = 0;
    const STATUS_SENDER_NO_AUTHORIZE = 1;
    const STATUS_SIZE_ERROR = 2;
    const STATUS_CUT_ERROR = 3;
    const STATUS_NO_AUTHENTICATE = 5;
    const STATUS_COMPANY_NO_AUTHORIZE = 6;
    const STATUS_INVALID_SCHEMA = 7;
    const STATUS_SIGNATURE_ERROR = 8;
    const STATUS_SYSTEM_BLOCK = 9;

    /**
     * Documento Recibido por el SII. Datos
     * Coinciden con los Registrados.
     */
    const RESPONSE_STATUS_DOK = 'DOK';

    /**
     * Documento Recibido por el SII pero Datos NO
     * Coinciden con los registrados.
     */
    const RESPONSE_STATUS_DNK = 'DNK';

    /**
     * Documento No Recibido por el SII.
     */
    const RESPONSE_STATUS_FAU = 'FAU';

    /**
     * Documento No Autorizado.
     */
    const RESPONSE_STATUS_FNA = 'FNA';

    /**
     * Documento Anulado.
     */
    const RESPONSE_STATUS_FAN = 'FAN';

    /**
     * Empresa no autorizada a Emitir Documentos
     * Tributarios Electrónicos
     */
    const RESPONSE_STATUS_EMP = "Empresa no autorizada a Emitir Documentos Tributarios Electrónicos";

    /**
     * Empresa no autorizada a Emitir Documentos
     * Tributarios Electrónicos
     */
    const RESPONSE_STATUS_TMD = 'TMD';

    /**
     * Existe Nota de Debito que Modifica Texto
     * Documento.
     */
    const RESPONSE_STATUS_TMC = 'TMC';

    /**
     * Existe Nota de Crédito que Modifica Textos
     * Documento
     */
    const RESPONSE_STATUS_MMD = 'MMD';

    /**
     * Existe Nota de Debito que Modifica Montos
     * Documento.
     */
    const RESPONSE_STATUS_MMC = 'MMC';

    /**
     * Existe Nota de Crédito que Modifica Montos
     * Documento.
     */
    const RESPONSE_STATUS_AND = 'AND';

    /**
     * Existe Nota de Crédito que Anula Documento
     */
    const RESPONSE_STATUS_ANC = 'ANC';

    /**
     * Traduccion mensajes
     */
    const RESPONSE_STATUS_MESSAGES = [
        self::RESPONSE_STATUS_DOK => "DOCUMENTO RECIBIDO POR EL SII. DATOS COINCIDEN CON LOS REGISTRADOS",
        self::RESPONSE_STATUS_DNK => "DOCUMENTO RECIBIDO POR EL SII PERO DATOS NO COINCIDEN CON LOS REGISTRADOS",
        self::RESPONSE_STATUS_FAU => "DOCUMENTO NO RECIBIDO POR EL SII",
        self::RESPONSE_STATUS_FNA => "DOCUMENTO NO AUTORIZADO",
        self::RESPONSE_STATUS_EMP => "EMPRESA NO AUTORIZADA A EMITIR DOCUMENTOS TRIBUTARIOS ELECTRÓNICOS",
        self::RESPONSE_STATUS_TMD => "EXISTE NOTA DE DEBITO QUE MODIFICA TEXTO DOCUMENTO",
        self::RESPONSE_STATUS_TMC => "EXISTE NOTA DE CRÉDITO QUE MODIFICA TEXTOS DOCUMENTO",
        self::RESPONSE_STATUS_MMD => "EXISTE NOTA DE DEBITO QUE MODIFICA MONTOS DOCUMENTO",
        self::RESPONSE_STATUS_MMC => "EXISTE NOTA DE CRÉDITO QUE MODIFICA MONTOS DOCUMENTO",
        self::RESPONSE_STATUS_AND => "EXISTE NOTA DE DEBITO QUE ANULA DOCUMENTO",
        self::RESPONSE_STATUS_ANC => "EXISTE NOTA DE CRÉDITO QUE ANULA DOCUMENTO"
    ];

    /**
     * Mensajes de los estados
     */
    const STATUS_MESSAGES = [
        self::STATUS_UPLOAD_OK            => 'UPLOAD OK',
        self::STATUS_SENDER_NO_AUTHORIZE  => 'EL SENDER NO TIENE PERMISO PARA ENVIAR',
        self::STATUS_SIZE_ERROR           => 'ERROR EN TAMAÑO DEL ARCHIVO (MUY GRANDE O MUY CHICO)',
        self::STATUS_CUT_ERROR            => 'ARCHIVO CORTADO (TAMAÑO <> AL PARÁMETRO SIZE)',
        self::STATUS_NO_AUTHENTICATE      => 'NO ESTÁ AUTENTICADO',
        self::STATUS_COMPANY_NO_AUTHORIZE => 'EMPRESA NO AUTORIZADA A ENVIAR ARCHIVOS',
        self::STATUS_INVALID_SCHEMA       => 'ESQUEMA INVALIDO',
        self::STATUS_SIGNATURE_ERROR      => 'FIRMA DEL DOCUMENTO',
        self::STATUS_SYSTEM_BLOCK         => 'SISTEMA BLOQUEADO',
    ];

    /**
     * @var FirmaElectronica
     */
    private $signature;

    /**
     * @var string|false|Token
     */
    protected $token;

    /**
     * @var id
     */
    private $id;

    /**
     * @param string $filename URL del archivo
     * @param string $pass Pass de la firma
     * @throws Exception
     * @version 2/12/21
     * @author  David Lopez <dlopez@hsd.cl>
     */
    public function __construct(array $config, string $id = null)
    {
        $keys = ['cert', 'pkey'];
        if (!Arr::validateKeys($config, $keys)) {
            throw new Exception("No se encuentran los datos para la conexión. Asegurese de que cert y pkey esten");
        }
        $this->signature = new FirmaElectronica($config);
        $this->id = $id;
    }

    /**
     * Obtendra el token desde SII
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    private function getToken()
    {
        if (empty($this->token)) {
            # Probablemente necesitemos utilizar alguna forma de cache
            if (!empty($this->id)) {
                $this->token = Cache::get("TOKEN-{$this->id}");
            }
            if (empty($this->token)) {
                $this->token = Autenticacion::getToken($this->signature);
                if (!empty($this->id)) {
                    # Token es valido por 6 horas
                    Cache::put("TOKEN-{$this->id}", $this->token, now()->addHours(6));
                }
            }

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

    /**
     * Consume el WDSL QueryEstDte
     *
     * Los parametros necesarios:
     * RutConsultante
     * DvConsultante
     * RutCompania
     * DvCompania
     * RutReceptor
     * DvReceptor
     * TipoDt
     * FolioDte
     * FechaEmisionDte
     * MontoDte
     *
     * El Token se genera automáticamente
     * @throws Exception
     * @version 13/12/21
     * @author  David Lopez <dlopez@hsd.cl>
     */
    public function statusDte(array $args)
    {
        # Validar los argumentos enviados correspondan
        $keys = [
            'RutConsultante',
            'DvConsultante',
            'RutCompania',
            'DvCompania',
            'RutReceptor',
            'DvReceptor',
            'TipoDte',
            'FolioDte',
            'FechaEmisionDte',
            'MontoDte'
        ];
        if (!Arr::validateKeys($args, $keys)) {
            throw new Exception("No cumple con los parametros necesarios");
        }
        # Hacer el llamado
        $xml = Sii::request(
            'QueryEstDte',
            'getEstDte',
            array_merge($args, ['Token' => $this->getToken()])
        );
        /**
         * ESTADO String 1-3 Código Estado S
         * GLOSA String 1-238 Detalle Código S
         * ERR_CODE String 1-3 Código Error S
         * GLOSA_ERR String 1-238 Glosa Error S
         * NUM_ATENCION String 1-40 Número de Atención,
         * Identificador de
         */
        # No hubo repuesta del sii
        if ($xml === false) {
            return false;
        }
        # Retornar lo devuelto por el SII
        return $xml->xpath('/SII:RESPUESTA/SII:RESP_HDR')[0];
    }

    /**
     * Consume el WDSL QueryEstUp
     * Los parametros necesarios:
     * Rut                      Corresponde al Rut Consultado
     * Dv                       Corresponde al DV (Digitio Verificador) del Rut Consultado
     * TrackId                  Corresponde al identificador de Envio
     *
     * El Token se genera automáticamente
     *
     * @throws Exception
     * @version 14/12/21
     * @author  David Lopez <dlopez@hsd.cl>
     */
    public function statusPacketDte(array $args)
    {
        # Validar los argumentos enviados correspondan
        $keys = [
            'Rut',
            'Dv',
            'TrackId',
        ];
        if (!Arr::validateKeys($args, $keys)) {
            throw new Exception("No cumple con los parametros necesarios");
        }
        # Hacer el llamado
        $xml = Sii::request(
            'QueryEstUp',
            'getEstUp',
            array_merge($args, ['Token' => $this->getToken()])
        );
        /**
         * TRACKID String numérico 1-10 identificador de Envió
         * ESTADO String numérico 1-3 Código Estado
         * GLOSA String Alfanumérico 1-40 Detalle Código
         * NUM_ATENCION String 1-40 Número de Atención,
         * Identificador de
         */
        # No hubo repuesta del sii
        if ($xml === false) {
            return false;
        }
        # Retornar lo devuelto por el SII
        $answer = (array)$xml->xpath('/SII:RESPUESTA/SII:RESP_HDR')[0];
        if ($body = $xml->xpath('/SII:RESPUESTA/SII:RESP_BODY')) {
            $answer = array_merge(
                $answer,
                ['DETALLE' => $body]
            );
        }

        return $answer;
    }
}
