<?php

namespace Application\EventBundle\Entity;

use Doctrine\ORM\EntityRepository;


/**
 * EventRepository
 *
 * @uses EntityRepository
 * @package EventBundle
 * @subpackage Entity
 * @author Eduardo Gulias <me@egulias.com>
 */
class EventRepository extends EntityRepository
{
    /**
     * findEvents
     *
     * @param int $max
     * @access public
     * @return Doctrine\Common\ArrayCollection
     */
    public function findEvents($max = 20)
    {
        return $this->_em->createQueryBuilder()
            ->add('select', 'e')
            ->add('from', 'ApplicationEventBundle:Event e')
            ->andWhere('e.date_start > :date')->setParameter('date', date('Y-m-d 00:00:00') )
            ->add('orderBy', 'e.date_start ASC')
            ->setMaxResults($max)->getQuery()->getResult();
    }

    /**
     * findEventsByUser
     *
     * @param int $user_id
     * @access public
     * @return Doctrine\Common\ArrayCollection
     */
    public function findEventsByUser($user_id)
    {
        return $this->_em->createQueryBuilder()
            ->add('select', 'u')
            ->add('from', 'ApplicationUserBundle:User u, ApplicationEventBundle:EventUser eu')
            ->andWhere('u.id = eu.user_id')
            ->andWhere('eu.user_id = :id')->setParameter('id', intval($user_id))->getQuery()->getResult();
    }

    /**
     * findUsersByEvent
     *
     * @param Application\EventBundle\Entity\Event $event
     * @param int $max
     * @access public
     * @return Doctrine\Common\ArrayCollection
     */
    public function findUsersByEvent($event,$max=12)
    {
        return $this->_em->createQueryBuilder()
            ->add('select', 'u')
            ->add('from', 'ApplicationUserBundle:User u, ApplicationEventBundle:EventUser eu')
            ->andWhere('u.id = eu.user_id')
            ->andWhere('eu.event_id = :id')->setParameter('id', $event->getId())
            ->setMaxResults($max)
            ->getQuery()->getResult();

    }

    /**
     * findEventsDQL
     *
     * @param \DateTime $from
     * @param \DateTime $to
     * @access public
     * @return Doctrine DQL
     */
    public function findEventsDQL(\DateTime $from, $to = NULL)
    {
        return $this->_em->createQueryBuilder()
            ->add('select', 'e')
            ->add('from', 'ApplicationEventBundle:Event e')
            ->andWhere('e.date_start > :date')->setParameter('date', $from->format('Y-m-d'))
            ->add('orderBy', 'e.featured DESC, e.date_start ASC');
    }

}
