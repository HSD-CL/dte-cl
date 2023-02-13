<?php
/**
 * @author David Lopez <dleo.lopez@gmail.com>
 * @version 5/8/20 7:21 p. m.
 */
namespace HSDCL\DteCl\Util;

use HSDCL\DteCl\Util\Exception;

/**
 * Class configuration
 * Mantendra un registro de los archivos de configuracion
 * @author David Lopez <dleo.lopez@gmail.com>
 */
final class Configuration
{
    /**
     * @var self[]
     */
    private static $instances = [];

    /**
     * @var string
     */
    private $filename;

    /**
     * @param string $alias
     * @param string $filename
     * @author David Lopez <dlopez@hsd.cl>
     */
    public function __construct(string $alias, string $filename)
    {
        $this->alias    = $alias;
        $this->filename = $filename;
    }

    /**
     * Retorna una configuracion para el DTE
     *
     * @param string $alias
     * @param string $filename
     * @return Configuration
     * @throws \HSDCL\DteCl\Util\Exception
     * @author
     */
    public static function getInstance(string $alias, string $filename): self
    {
        $realPath = \realpath($filename);

        if ($realPath === false) {
            throw new Exception(
                \sprintf(
                    'Could not read "%s".',
                    $filename
                )
            );
        }

        if (!isset(self::$instances[$alias])) {
            self::$instances[$alias] = new self($alias, $filename);
        }

        return self::$instances[$alias];
    }

    /**
     * Retorna el path real para el archivo de configuracion
     * @return string
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

}
