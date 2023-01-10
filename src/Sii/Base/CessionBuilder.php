<?php
/**
 * @version 1/12/22 5:54 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Sii\Base;

use HSDCL\DteCl\Util\Exception;
use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Sii;
use sasco\LibreDTE\Sii\EnvioDte;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CessionBuilder
 *
 * Permite crear la cesion de un documento
 * @package HSDCL\DteCl\Sii\Base
 * @author  David Lopez <dleo.lopez@gmail.com>
 */
class CessionBuilder
{
    /**
     * @var FirmaElectronica
     */
    protected $signature;

    /**
     * @var string
     */
    protected $xml;

    /**
     * @var array|null
     */
    protected $assignee;

    /**
     * @var array|null
     */
    protected $assignor;

    /**
     * Entorno
     * @var int
     */
    protected $environment;

    /**
     * Agente que construye el documento cedito
     * @var
     */
    protected $agent;

    /**
     * DTE a ceder
     * @var
     */
    protected $dte;

    /**
     * Cesion
     * @var
     */
    protected $cession;

    /**
     * DTE Cedido
     * @var
     */
    protected $dteAssignment;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param FirmaElectronica $signature
     * @param string $xml
     * @param array|null $assignee Cesionario
     * @param array|null $assignor Cedente
     * @param int $environment
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function __construct(FirmaElectronica $signature,
                                string           $xml,
                                array            $assignee,
                                array            $assignor,
                                int              $environment = Sii::CERTIFICACION,
                                array            $data = []
    )
    {
        $this->xml = $xml;
        $this->signature = $signature;
        $resolverAssignee = new OptionsResolver();
        $this->configureOptionsForAssignee($resolverAssignee);
        $this->assignee = $resolverAssignee->resolve($assignee);
        $resolverAssignor = new OptionsResolver();
        $this->configureOptionsForAssignor($resolverAssignor);
        $this->assignor = $resolverAssignor->resolve($assignor);
        $this->environment = $environment;
        if (!empty($data)) {
            $resolverData = new OptionsResolver();
            $this->configureOptionsForData($resolverData);
            $this->data = $resolverData->resolve($data);
        }
    }

    /**
     * Configuración de los datos del cedente
     *
     * @param OptionsResolver $resolver
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    protected function configureOptionsForAssignor(OptionsResolver $resolver)
    {
        $resolver->setDefined(['eMail', 'RUTAutorizado']);
        $resolver->setDefault('RUTAutorizado', function (OptionsResolver $spoolResolver) {
            $spoolResolver->setDefined(['RUT', 'Nombre']);
            $spoolResolver->setRequired(['RUT', 'Nombre']);
        });
    }

    /**
     * Configuracion de los datos del cesionario
     *
     * @param OptionsResolver $resolver
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    protected function configureOptionsForAssignee(OptionsResolver $resolver)
    {
        $resolver->setDefined(['RUT', 'RazonSocial', 'Direccion', 'eMail', 'DeclaracionJurada']);
        $resolver->setRequired(['RUT', 'RazonSocial', 'Direccion', 'eMail']);
    }

    /**
     * Configuracion de los datos extra para la cesión
     *
     * @param OptionsResolver $resolver
     * @author David Lopez <dleo.lopez@gmail.com>
     * @version 10/1/23
     */
    protected function configureOptionsForData(OptionsResolver $resolver)
    {
        $resolver->setDefined(['MontoCesion', 'UltimoVencimiento', 'OtrasCondiciones', 'eMailDeudor']);
    }

    /**
     * @version 1/12/22
     * @author  David Lopez <dleo.lopez@gmail.com>
     */
    public function parse(): self
    {
        $agent = new EnvioDte();
        $agent->loadXML($this->xml);
        $this->dte = $agent->getDocumentos()[0];

        return $this;
    }

    /**
     * Enviar la cesion
     *
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function send()
    {
        if (empty($this->agent)) {
            throw new Exception("No esta definido el agente");
        }

        return $this->agent->enviar();
    }

    /**
     * @param array|null $startFolio
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function setStampAndSign(): self
    {
        if (empty($this->dte)) {
            throw new Exception("El DTE no ha sido parseado");
        }
        $this->dteAssignment = new Sii\Factoring\DteCedido($this->dte);
        $this->dteAssignment->firmar($this->signature);

        return $this;
    }

    /**
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function setSign(): self
    {
        if (empty($this->dteAssignment)) {
            throw new Exception("El DTE cedido no esta definido");
        }
        $this->cession = new Sii\Factoring\Cesion($this->dteAssignment);
        $this->cession->setCesionario($this->assignee);
        $this->cession->setCedente($this->assignor);
        if (!empty($this->data)) {
            $this->cession->setDatos($this->data);
        }
        $this->cession->firmar($this->signature);

        return $this;
    }

    /**
     * @version 2/12/22
     * @author  David Lopez <dleo.lopez@gmail.com>
     */
    public function setAgent(): self
    {
        $this->agent = new Sii\Factoring\Aec();
        $this->agent->setFirma($this->signature);
        $this->agent->agregarDteCedido($this->dteAssignment);
        $this->agent->agregarCesion($this->cession);

        return $this;
    }

    /**
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function build(): self
    {
        $this->parse()
            ->setStampAndSign()
            ->setSign()
            ->setAgent();

        return $this;
    }

    /**
     * @param string $filename
     * @return mixed
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function exportData()
    {
        if (empty($this->agent)) {
            throw new Exception("No esta definido el agente");
        }

        return $this->agent->generar();
    }
}
