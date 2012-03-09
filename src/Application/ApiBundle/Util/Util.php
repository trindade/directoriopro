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

    /**
     * eventsDetailsGenerator
     *
     * @param array $events
     * @param \Doctrine\ORM\EntityRepository $repo
     * @static
     * @access public
     * @return array
     */
    static public function eventsDetailsGenerator(array $events, \Application\EventBundle\Entity\EventRepository $repo )
    {
        $date_now = false;

        foreach ($events as $event) {
            $date_current = $event->getDateStart()->format('Y-m-d');
            $event->date_now = false;

            if ( $date_now != $date_current ) {
                $date_now = $date_current;
                $event->date_now = $event->getPrettyDate();
            }
            $event->users_list = $repo->findUsersByEvent($event);
        }
        return $events;
    }
}
