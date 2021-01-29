<?php
/**
 * @version 25/8/20 9:06 p. m.
 * @author  Danilo Vasquez <dvasquezr.ko@gmail.com>
 */

namespace HSDCL\DteCl\Console\Commands;


use HSDCL\DteCl\Sii\Certification\FileSource;
use HSDCL\DteCl\Sii\Certification\ShipmentBookCertificactionBuilder;
use Illuminate\Console\Command;
use sasco\LibreDTE\FirmaElectronica;

class ShipmentBookCertificactionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dte:office-guide-book-certification {--firma} {--source} {--pass} {--output} {--RutEmisorLibro} {--FchResol} {--NroResol} {--FolioNotificacion}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Office guide book certification command';

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
            'RutEmisorLibro' => $this->option('RutEmisorLibro'),
            'FchResol' => $this->option('FchResol'),
            'NroResol' => $this->option('NroResol'),
            'FolioNotificacion' => $this->option('FolioNotificacion')
        ];
        # Instaciar el builder para la certificacion
        $certification = new ShipmentBookCertificactionBuilder($sign, new FileSource($this->option('source')));
        $certification->build([], $caratula);
        $certification->export($this->option('output'));
    }
}
