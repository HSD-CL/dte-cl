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

    /**
     * http://uk1.php.net/array_walk_recursive implementation that is used to remove nodes from the array.
     *
     * @param array The input array.
     * @param callable $callback Function must return boolean value indicating whether to remove the node.
     * @return array
     */
    public static function walkRecursiveRemove (array $array, callable $callback) {
        foreach ($array as $k => $v) {
            if ($callback($v, $k)) {
                unset($array[$k]);
                continue;
            }
            if (is_array($v)) {
                $array[$k] = self::walkRecursiveRemove($v, $callback);
            }
        }

        return $array;
    }
}
