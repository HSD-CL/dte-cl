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
    protected $signature = 'dte:purchase-book-certification {--firma} {--source} {--pass} {--output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purchase book certification command';

    /**
     * Execute the console command.
     *
     * @return
     */
    public function handle()
    {
        # Constuir la firma desde el comando
        $sign = new FirmaElectronica(['file' => $this->option('firma'), 'pass' => $this->option('pass')]);
        # Construir la caratula
        $caratula = [
            'RutEmisorLibro' => '78465260-2',
            'RutEnvia' => '12021283-4',
            'PeriodoTributario' => '2000-03',
            'FchResol' => '2020-07-27',
            'NroResol' => 102006,
            'TipoOperacion' => 'COMPRA',
            'TipoLibro' => 'ESPECIAL',
            'TipoEnvio' => 'TOTAL',
            'FolioNotificacion' => 102006,
        ];
        # Instaciar el builder para la certificacion
        $certification = new PurchaseBookCertificactionBuilder($sign, new FileSource($this->option('source')));
        $certification->build([], $caratula);
        $certification->export($this->option('output'));
    }
}
