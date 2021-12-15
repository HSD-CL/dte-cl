<?php

namespace HSDCL\DteCl\Tests\Feature;

use HSDCL\DteCl\Sii\Base\Dte;
use HSDCL\DteCl\Sii\Base\DteBuilder;
use HSDCL\DteCl\Sii\Base\JsonSource;
use HSDCL\DteCl\Sii\Certification\PacketDteBuilder;
use HSDCL\DteCl\Sii\Certification\ExemptCertificationBuilder;
use HSDCL\DteCl\Sii\Certification\FileSource;
use HSDCL\DteCl\Sii\Certification\BasicCertificationBuilder;
use HSDCL\DteCl\Sii\Request;
use HSDCL\DteCl\Util\Configuration;
use HSDCL\DteCl\Tests\TestCase;
use HSDCL\DteCl\Util\Exception;
use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Sii\Folios;

/**
 * Class RequestTEst
 * @package HSDCL\DteCl\Tests
 * @author  David Lopez <dleo.lopez@gmail.com>
 */
class RequestTest extends TestCase
{
    /**
     * @version 14/12/21
     * @author  David Lopez <dlopez@hsd.cl>
     */
    public function testExpectectExceptionWithEmptyRequest()
    {
        $this->expectException(Exception::class);
        $request = new Request([]);
    }

    /**
     * @version 14/12/21
     * @author  David Lopez <dlopez@hsd.cl>
     */
    public function testCanInstantiate(): Request
    {
        $config = ['cert' => <<<END

END,
                   'pkey' => <<<END

END
        ];
        $request = new Request($config, 'test');
        $this->assertInstanceOf(Request::class, $request);

        return $request;
    }

    /**
     * @depends testCanInstantiate
     * @version 14/12/21
     * @author  David Lopez <dlopez@hsd.cl>
     */
    public function testStatusDte(Request $request)
    {
        # Assert exception
        $this->expectException(Exception::class);
        $request->statusDte([]);
    }

    /**
     * @version 14/12/21
     * @depends testCanInstantiate
     * @author  David Lopez <dlopez@hsd.cl>
     */
    public function testPacketDteStatusThrowsException(Request $request)
    {
        $this->expectException(Exception::class);
        $request->statusPacketDte([]);
    }

    /**
     * @version 14/12/21
     * @depends testCanInstantiate
     * @author  David Lopez <dlopez@hsd.cl>
     */
    public function testPacketDteStatus(Request $request)
    {
        $this->assertIsArray($request->statusPacketDte([
            'Rut'     => '',
            'Dv'      => '',
            'TrackId' => '',
        ]));
    }
}
