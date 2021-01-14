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
    protected $signature = 'dte:sales-book-certification {--firma} {--source} {--pass} {--output}';

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
            'RutEmisorLibro' => '78465260-2',
            'RutEnvia' => '12021283-4',
            'PeriodoTributario' => '2000-07',
            'FchResol' => '2020-07-27',
            'NroResol' => 102006,
            'TipoOperacion' => 'VENTA',
            'TipoLibro' => 'ESPECIAL',
            'TipoEnvio' => 'TOTAL',
            'FolioNotificacion' => 102006,
        ];
        # Instaciar el builder para la certificacion
        $certification = new SalesBookCertificactionBuilder($sign, new FileSource($this->option('source')));
        $certification->build([], $caratula);
        $certification->export($this->option('output'));
    }
}
