<?php
/**
 * @version 2/12/22 10:52 a. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Tests\Feature;

use HSDCL\DteCl\Sii\Base\CessionBuilder;
use HSDCL\DteCl\Sii\Base\DteBuilder;
use HSDCL\DteCl\Tests\TestCase;

/**
 * Class CessionBuilderTest
 * @version 02/12/22
 * @author  David Lopez <dleo.lopez@gmail.com>
 * @package Feature
 */
class CessionBuilderTest extends TestCase
{
    protected $xml;

    protected $signature;

    /**
     * @version 2/12/22
     * @author  David Lopez <dleo.lopez@gmail.com>
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->xml = file_get_contents(__DIR__ . '/../../resources/assets/xml/set_basico/1.xml');
        $this->signature = DteBuilder::makeSignature([
            'file' => file_get_contents(__DIR__ . '/../../resources/assets/certs/cert.crt'),
            'pass' => 'M3m3n700'
        ]);
    }

    /**
     * @author David Lopez <dleo.lopez@gmail.com>
     * @test
     **/
    public function canInstanciate()
    {
        $assignee = [
            'RUT'         => '55666777-8',
            'RazonSocial' => 'Empresa de Factoring SpA',
            'Direccion'   => 'Santiago',
            'eMail'       => 'cesionario@example.com',
        ];
        $assignor = [
            'eMail' => 'cedente@example.com',
            'RUTAutorizado' => [
                'RUT' => $this->signature->getID(),
                'Nombre' => $this->signature->getName(),
            ],
        ];
        $builder = new CessionBuilder($this->signature, $this->xml, $assignee, $assignor);
        $this->assertInstanceOf(CessionBuilder::class, $builder);
    }

    /**
     * @author David Lopez <dleo.lopez@gmail.com>
     * @test
     **/
    public function canParse()
    {
        $this->markTestSkipped();
    }
}
