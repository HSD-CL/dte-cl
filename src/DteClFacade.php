<?php

namespace HSDCL\DteCl;

use Illuminate\Support\Facades\Facade;

/**
 * @see \HSDCL\DteCl\Skeleton\SkeletonClass
 */
class DteClFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'dte-cl';
    }
}
