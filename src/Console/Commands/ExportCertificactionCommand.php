<?php

/**
 * @version 25/8/20 9:06 p. m.
 * @author  Danilo Vasquez <dvasquezr.ko@gmail.com>
 */

namespace HSDCL\DteCl\Console\Commands;


use HSDCL\DteCl\Sii\Certification\FileSource;
use HSDCL\DteCl\Sii\Certification\ExportCertificactionBuilder;
use Illuminate\Console\Command;
use HSDCL\DteCl\Util\Configuration;
use sasco\LibreDTE\FirmaElectronica;
use \sasco\LibreDTE\Sii\Folios;

/**
 * Class ExportCertificactionCommand
 * @package HSDCL\DteCl\Console\Commands
 */
class ExportCertificactionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "dte:export-certification {--firma} {--source} {--folios-fe} {--folios-nc} {--folios-nd} {--pass} {--output}
    {--resolucion} {--start-folio-fe} {--start-folio-nc} {--start-folio-nd} {--RUTEmisor} {--RznSoc} {--GiroEmis} {--Acteco} {--DirOrigen} {--CmnaOrigen}
    {--RUTRecep} {--RznSocRecep} {--GiroRecep} {--DirRecep} {--CmnaRecep} {--RutEnvia} {--RutReceptor}";


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para la certificación del set de exportación';

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        # Constuir la firma desde la configuracion
        $firma = new FirmaElectronica(['file' => $this->option('firma'), 'pass' => $this->option('pass')]);
        # Construir los folios desde los archivos

        $folios[110] = new Folios(
            file_get_contents(Configuration::getInstance('folios-fe', $this->option('folios-fe'))->getFilename())
        );

        $folios[112] = new Folios(
            file_get_contents(Configuration::getInstance('folios-nc', $this->option('folios-nc'))->getFilename())
        );

        $folios[111] = new Folios(
            file_get_contents(Configuration::getInstance('folios-nd', $this->option('folios-nd'))->getFilename())
        );

        # Construir la caratula
        $caratula = [
            'RutEnvia'    => $this->option('RutEnvia'),
            'RutReceptor' => $this->option('RutReceptor'),
            'FchResol'    => $this->option('resolucion'),
            'NroResol'    => 0,
        ];

        $emisor = [
            'RUTEmisor'  => $this->option('RUTEmisor'),
            'RznSoc'     => $this->option('RznSoc'),
            'GiroEmis'   => $this->option('GiroEmis'),
            'Acteco'     => $this->option('Acteco'),
            'DirOrigen'  => $this->option('DirOrigen'),
            'CmnaOrigen' => $this->option('CmnaOrigen')
        ];
        $receptor = [
            'RUTRecep'    => $this->option('RUTRecep'),
            'RznSocRecep' => $this->option('RznSocRecep'),
            'GiroRecep'   => $this->option('GiroRecep'),
            'DirRecep'    => $this->option('DirRecep'),
            'CmnaRecep'   => $this->option('CmnaRecep')
        ];

        $startFolios = [
            110 => $this->option('start-folio-fe'),
            112 => $this->option('start-folio-nc'),
            111 => $this->option('start-folio-nd')
        ];

        # Instaciar el builder para la certificacion
        $certification = new ExportCertificactionBuilder($firma, new FileSource($this->option('source')), $folios, $emisor, $receptor);
        $certification->build($startFolios, $caratula);
        $certification->export($this->option('output'));
    }
}
