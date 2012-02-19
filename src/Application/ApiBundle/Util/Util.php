<?php

namespace Application\ApiBundle\Util;

/**
 * Utility class
 *
 * @package ApiBundle
 * @subpackage Util
 * @author Eduardo Gulias <me@egulias.com>
 */
class Util
{
    /**
     * Slug generation method slugify
     *
     * @param string $str
     * @param array $replace
     * @param string $delimiter
     * @static
     * @access public
     * @return string
     */
    static public function slugify($str, array $replace = array(), $delimiter='-')
    {
        $r = array(' con ', ' de ', ' para ', ' y ', ' en ', ' of ');
        if (!empty($replace))$r = $replace;

        $str = str_replace($r, ' ', $str);

        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
        $clean = strtolower(trim($clean, '-'));
        $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

        return $clean;
    }
}
