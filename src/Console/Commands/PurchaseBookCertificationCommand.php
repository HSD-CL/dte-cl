<?php
/**
 * @version 25/8/20 9:06 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Console\Commands;


use HSDCL\DteCl\Sii\Certification\FileSource;
use HSDCL\DteCl\Sii\Certification\PurchaseBookCertificactionBuilder;
use Illuminate\Console\Command;
use sasco\LibreDTE\FirmaElectronica;

class PurchaseBookCertificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "dte:purchase-book-certification {--firma} {--source} {--pass} {--output} {--RutEmisorLibro}
        {--RutEnvia} {--PeriodoTributario} {--FchResol} {--NroResol} {--FolioNotificacion}
    ";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purchase book certification command';

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
            'TipoOperacion'     => 'COMPRA',
            'TipoLibro'         => 'ESPECIAL',
            'TipoEnvio'         => 'TOTAL',
            'FolioNotificacion' => $this->option('FolioNotificacion'),
        ];
        # Instanciar el builder para la certificacion
        $certification = new PurchaseBookCertificactionBuilder($sign, new FileSource($this->option('source')));
        $certification->build([], $caratula);
        $certification->export($this->option('output'));
    }
}
