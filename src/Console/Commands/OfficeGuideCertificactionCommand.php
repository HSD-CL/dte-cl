<?php
/**
 * @version 25/8/20 9:06 p. m.
 * @author  Danilo Vasquez <dvasquezr.ko@gmail.com>
 */

namespace HSDCL\DteCl\Console\Commands;


use HSDCL\DteCl\Sii\Certification\FileSource;
use HSDCL\DteCl\Sii\Certification\OfficeGuideCertificactionBuilder;
use Illuminate\Console\Command;
use sasco\LibreDTE\FirmaElectronica;

class OfficeGuideCertificactionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dte:office-guide-certification {--firma} {--source} {--pass} {--output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Office guide certification command';

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
            'RutReceptor' => '78465260-2',
            'FchResol' => '2020-11-04',
            'NroResol' => 102006
        ];
        # Instaciar el builder para la certificacion
        $certification = new OfficeGuideCertificactionBuilder($sign, new FileSource($this->option('source')));
        $certification->build([], $caratula);
        $certification->export($this->option('output'));
    }
}
