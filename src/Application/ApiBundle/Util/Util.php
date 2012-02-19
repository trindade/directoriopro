<?php

namespace Application\ApiBundle\Util;

class Util
{
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
