<?php

namespace HSDCL\DteCl\Util;

/**
 * Class XmlEncoding
 *
 * Corrige la codificación de los XML de DTE cuyo contenido no coincide con la
 * codificación que declaran en su cabecera.
 *
 * @package HSDCL\DteCl\Util
 * @author  Danilo Vásquez
 */
abstract class XmlEncoding
{
    /**
     * Marca de orden de bytes (BOM) de UTF-8
     */
    const BOM_UTF8 = "\xEF\xBB\xBF";

    /**
     * Normaliza un XML que declara ISO-8859-1 pero cuyo contenido es UTF-8.
     *
     * Ese desajuste hace que el parser lea cada byte como un carácter y que
     * "ñ" termine como "Ã±". Solo se convierte cuando se cumplen las tres
     * condiciones: la cabecera declara ISO-8859-1, hay bytes fuera de ASCII y
     * el contenido completo es UTF-8 válido. Un texto realmente codificado en
     * ISO-8859-1 con acentos no es UTF-8 válido, así que no se toca.
     *
     * El contenido se convierte a ISO-8859-1 para que coincida con la
     * cabecera. Si tiene caracteres que no existen en ISO-8859-1, se conserva
     * en UTF-8 y se corrige la cabecera, para no perderlos.
     *
     * @param string $xml
     * @return string
     */
    public static function normalize(string $xml): string
    {
        if (!self::declaresIso88591($xml) || !self::hasNonAscii($xml) || !mb_check_encoding($xml, 'UTF-8')) {
            return $xml;
        }
        $utf8 = self::withoutBom($xml);
        $iso = mb_convert_encoding($utf8, 'ISO-8859-1', 'UTF-8');
        if (mb_convert_encoding($iso, 'UTF-8', 'ISO-8859-1') === $utf8) {
            return $iso;
        }

        return preg_replace(self::declarationPattern(), '$1UTF-8$3', $utf8, 1);
    }

    /**
     * Indica si la declaración XML señala ISO-8859-1 como codificación
     *
     * @param string $xml
     * @return bool
     */
    private static function declaresIso88591(string $xml): bool
    {
        return preg_match(self::declarationPattern(), $xml, $matches) === 1
            && strcasecmp($matches[2], 'ISO-8859-1') === 0;
    }

    /**
     * Indica si hay al menos un byte fuera del rango ASCII
     *
     * @param string $xml
     * @return bool
     */
    private static function hasNonAscii(string $xml): bool
    {
        return preg_match('/[\x80-\xFF]/', $xml) === 1;
    }

    /**
     * Quita el BOM de UTF-8 si el contenido comienza con él
     *
     * @param string $xml
     * @return string
     */
    private static function withoutBom(string $xml): string
    {
        return strncmp($xml, self::BOM_UTF8, 3) === 0 ? substr($xml, 3) : $xml;
    }

    /**
     * Expresión que captura la codificación de la declaración XML:
     * grupo 1 hasta la comilla de apertura, grupo 2 el nombre de la
     * codificación y grupo 3 la comilla de cierre.
     *
     * @return string
     */
    private static function declarationPattern(): string
    {
        return '/^((?:\xEF\xBB\xBF)?\s*<\?xml[^>]*?encoding\s*=\s*["\'])([^"\']+)(["\'])/i';
    }
}
