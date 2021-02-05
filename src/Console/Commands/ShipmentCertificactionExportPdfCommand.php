<?php

/**
 * @version 25/8/20 9:06 p. m.
 * @author  Danilo Vasquez <dvasquezr.ko@gmail.com>
 */

namespace HSDCL\DteCl\Console\Commands;

use HSDCL\DteCl\Sii\Certification\ShipmentCertificactionExportPdfBuilder;
use Illuminate\Console\Command;

class ShipmentCertificactionExportPdfCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "dte:office-guide-export-pdf {--source} {--logo} {--output}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Office guide export pdf command';

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        # Instaciar el builder para la certificacion
        $certification = new ShipmentCertificactionExportPdfBuilder($this->option('source'), $this->option('logo'), $this->option('output'));
        $certification->build();
    }
}
