<?php
/**
 * @version 14/1/21 1:39 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Console\Commands;


use HSDCL\DteCl\Sii\Base\Dte;
use HSDCL\DteCl\Sii\Certification\ExemptCertificationBuilder;
use HSDCL\DteCl\Sii\Certification\FileSource;
use HSDCL\DteCl\Util\Configuration;
use Illuminate\Console\Command;
use sasco\LibreDTE\FirmaElectronica;
use sasco\LibreDTE\Sii\Folios;

/**
 * Class ExemptCertificationCommand
 *
 * Comando para certificar las facturas exentas
 * @package HSDCL\DteCl\Console\Commands
 * @author  David Lopez <dleo.lopez@gmail.com>
 */
class ExemptCertificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dte:exempt-certification {--resolucion} {--folios-fe} {--folios-nc} {--folios-nd}
        {--start-fe=1} {--start-nc=1} {--start-nd=1} {--firma} {--source} {--pass} {--output}
        {--RUTEmisor} {--RznSoc} {--GiroEmis} {--Acteco} {--DirOrigen} {--CmnaOrigen} {--RUTRecep} {--RznSocRecep}
        {--GiroRecep} {--DirRecep} {--CmnaRecep} {--RutEnvia} {--RutReceptor}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para la certificacion de facturas exentas';

    /**
     * Execute the console command.
     *
     */
    public function handle(): void
    {
        # Constuir la firma desde la configuracion
        $firma = new FirmaElectronica(['file' => $this->option('firma'), 'pass' => $this->option('pass')]);
        # Construir los folios desde los archivos
        $folios[Dte::FACTURA_EXENTA_ELECTRONICA] = new Folios(
            file_get_contents(Configuration::getInstance('folios-' . Dte::FACTURA_EXENTA_ELECTRONICA, $this->option('folios-fe'))->getFilename())
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
            'CmnaOrigen' => $this->option('CmnaOrigen')
        ];
        $receptor = [
            'RUTRecep'    => $this->option('RUTRecep'),
            'RznSocRecep' => $this->option('RznSocRecep'),
            'GiroRecep'   => $this->option('GiroRecep'),
            'DirRecep'    => $this->option('DirRecep'),
            'CmnaRecep'   => $this->option('CmnaRecep')
        ];
        $builder = new ExemptCertificationBuilder($firma, new FileSource($this->option('source')), $folios, $emisor, $receptor);
        $caratula = [
            'RutEnvia'    => $this->option('RutEnvia'),
            'RutReceptor' => $this->option('RutReceptor'),
            'FchResol'    => $this->option('resolucion'),
            'NroResol'    => 0,
        ];
        $startFolios = [
            Dte::FACTURA_EXENTA_ELECTRONICA  => $this->option('start-fe'),
            Dte::NOTA_DE_DEBITO_ELECTRONICA  => $this->option('start-nd'),
            Dte::NOTA_DE_CREDITO_ELECTRONICA => $this->option('start-nc')
        ];

        $builder->build($startFolios, $caratula);
        $builder->export($this->option('output'));
    }
}
