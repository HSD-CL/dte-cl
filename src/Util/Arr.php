<?php
/**
 * @version 14/12/21 1:05 p. m.
 * @author  David Lopez <dleo.lopez@gmail.com>
 */

namespace HSDCL\DteCl\Util;

/**
 * Class Arr
 *
 * Handle utils method for array
 * @package HSDCL\DteCl\Util
 * @author  David Lopez <dleo.lopez@gmail.com>
 */
class Arr
{
    /**
     * Validate that an array has keys neeeded
     * @param array $data
     * @param array $keys
     * @return bool
     * @author David Lopez <dleo.lopez@gmail.com>
     */
    public static function validateKeys(array $data, array $keys): bool
    {

        $keysArgs = array_keys($data);
        if (!empty(array_diff($keys, $keysArgs))) {
            return false;
        }

        return true;
    }
}
