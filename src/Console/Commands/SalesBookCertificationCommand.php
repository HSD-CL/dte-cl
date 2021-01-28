<?php

/**
 * @version 25/8/20 9:06 p. m.
 * @author  Danilo Vasquez
 */

namespace HSDCL\DteCl\Console\Commands;


use HSDCL\DteCl\Sii\Certification\FileSource;
use HSDCL\DteCl\Sii\Certification\SalesBookCertificactionBuilder;
use Illuminate\Console\Command;
use sasco\LibreDTE\FirmaElectronica;

class SalesBookCertificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "dte:sales-book-certification {--firma} {--source} {--pass} {--output} {--RutEmisorLibro} {--RutEnvia} 
    {--PeriodoTributario} {--FchResol} {--NroResol} {--TipoOperacion} {--TipoLibro} {--TipoEnvio} {--FolioNotificacion}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sales book certification command';

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        # Constuir la firma desde el comando
        $sign = new FirmaElectronica(['file' => $this->option('firma'), 'pass' => $this->option('pass')]);
        # Construir la caratula
        $caratula = [
            'RutEmisorLibro'    => $this->option('RutEmisorLibro'),
            'RutEnvia'          => $this->option('RutEnvia'),
            'PeriodoTributario' => $this->option('PeriodoTributario'),
            'FchResol'          => $this->option('FchResol'),
            'NroResol'          => $this->option('NroResol'),
            'TipoOperacion'     => $this->option('TipoOperacion'),
            'TipoLibro'         => $this->option('TipoLibro'),
            'TipoEnvio'         => $this->option('TipoEnvio'),
            'FolioNotificacion' => $this->option('FolioNotificacion'),
        ];
        # Instaciar el builder para la certificacion
        $certification = new SalesBookCertificactionBuilder($sign, new FileSource($this->option('source')));
        $certification->build([], $caratula);
        $certification->export($this->option('output'));
    }
}
