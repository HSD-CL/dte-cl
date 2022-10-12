<?php

namespace HSDCL\DteCl\Console\Commands;

use Carbon\Carbon;
use HSDCL\DteCl\Sii\Base\Dte;
use HSDCL\DteCl\Sii\Base\Pdf\PdfDte;
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
class BasicCertificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "dte:basic-certification {--resolucion} {--folios-fe} {--folios-nc} {--folios-nd}
        {--start-fe=1} {--start-nc=1} {--start-nd=1} {--firma} {--source} {--pass} {--output}
        {--RUTEmisor} {--RznSoc} {--GiroEmis} {--Acteco} {--DirOrigen} {--CmnaOrigen} {--RUTRecep} {--RznSocRecep}
        {--GiroRecep} {--DirRecep} {--CmnaRecep} {--RutEnvia} {--RutReceptor}
    ";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Proceso de certificación básico';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        # Constuir la firma desde la configuracion
        $firma = BasicCertificationBuilder::makeFirma($this->option('firma'), $this->option('pass'));
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
            'RUTEmisor'  => $this->option('RUTEmisor'),
            'RznSoc'     => $this->option('RznSoc'),
            'GiroEmis'   => $this->option('GiroEmis'),
            'Acteco'     => $this->option('Acteco'),
            'DirOrigen'  => $this->option('DirOrigen'),
            'CmnaOrigen' => $this->option('CmnaOrigen') #'Monte Patria',
        ];
        $receptor = [
            'RUTRecep'    => $this->option('RUTRecep'),    #'81515100-3',
            'RznSocRecep' => $this->option('RznSocRecep'), #'SELIM DABED SPA.',
            'GiroRecep'   => $this->option('GiroRecep'),   #'BARRACA Y FERRETERIA',
            'DirRecep'    => $this->option('DirRecep'),    #'BENAVENTE 516',
            'CmnaRecep'   => $this->option('CmnaRecep')    #'OVALLE',
        ];
        $builder = new BasicCertificationBuilder($firma, new FileSource($this->option('source')), $folios, $emisor, $receptor);
        $caratula = [
            'RutEnvia'    => $this->option('RutEnvia'),    #'12021283-4',
            'RutReceptor' => $this->option('RutReceptor'), #'60803000-K',
            'FchResol'    => $this->option('resolucion'),
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
