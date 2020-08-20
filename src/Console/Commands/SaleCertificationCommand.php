<?php

namespace HSDCL\DteCl\Console\Commands;

use Carbon\Carbon;
use HSDCL\DteCl\Sii\Base\Dte;
use HSDCL\DteCl\Sii\Certification\FileSource;
use HSDCL\DteCl\Sii\Certification\BasicCertificationBuilder;
use HSDCL\DteCl\Util\Configuration;
use \Illuminate\Console\Command;
use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Sii\Folios;

/**
 * @author David Lopez <dleo.lopez@gmail.com>
 * @version 11/8/20 7:35 p. m.
 */
class SaleCertificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dte:sale-certification {--folios-fe} {--folios-nc} {--folios-nd} {--start-fe=1} {--start-nc=1} {--start-nd=1} {--firma} {--source} {--pass} {--output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sale Certification Proccess';

    /**
     * Execute the console command.
     *
     * @return
     */
    public function handle()
    {
        # Constuir la firma desde la configuracion
        $firma = new FirmaElectronica(['file' => $this->option('firma'), 'pass' => $this->option('pass')]);
        # Construir los folios desde los archivos

        $folios[Dte::FACTURA_ELECTRONICA] = new Folios(
            file_get_contents(Configuration::getInstance('folios-' . Dte::FACTURA_ELECTRONICA, $this->option('folios-fe'))->getFilename())
        );

        $folios[Dte::NOTA_DE_CREDITO_ELECTRONICA] = new Folios(
            file_get_contents(Configuration::getInstance(
                'folios-' . Dte::NOTA_DE_CREDITO_ELECTRONICA,
                $this->option('folios-nc'))->getFilename()
            )
        );
        $folios[Dte::NOTA_DE_DEBITO_ELECTRONICA] = new Folios(
            file_get_contents(Configuration::getInstance(
                'folios-' . Dte::NOTA_DE_DEBITO_ELECTRONICA,
                $this->option('folios-nd'))->getFilename()
            )
        );
        $emisor = [
            'RUTEmisor'  => '78465260-2',
            'RznSoc'     => 'INVERSIONES ANTUMALAL LIMITADA',
            'GiroEmis'   => 'AGRICOLA',
            'Acteco'     => 726000,
            'DirOrigen'  => 'FUNDO POTRERILLOS S N',
            'CmnaOrigen' => 'Monte Patria',
        ];
        $receptor = [
            'RUTRecep'    => '81515100-3',
            'RznSocRecep' => 'SELIM DABED SPA.',
            'GiroRecep'   => 'BARRACA Y FERRETERIA',
            'DirRecep'    => 'BENAVENTE 516',
            'CmnaRecep'   => 'OVALLE',
        ];
        $builder = new BasicCertificationBuilder($firma, $folios, new FileSource($this->option('source')), $emisor, $receptor);
        $caratula = [
            'RutEnvia'    => '12021283-4',
            'RutReceptor' => '60803000-K',
            'FchResol'    => '2020-07-27',
            'NroResol'    => 0,
        ];
        $startFolios = [
            Dte::FACTURA_ELECTRONICA         => $this->option('start-fe'),
            Dte::NOTA_DE_DEBITO_ELECTRONICA  => $this->option('start-nd'),
            Dte::NOTA_DE_CREDITO_ELECTRONICA => $this->option('start-nc')
        ];

        $builder->build($startFolios, $caratula);
        $builder->export($this->option('output'));
    }
}
