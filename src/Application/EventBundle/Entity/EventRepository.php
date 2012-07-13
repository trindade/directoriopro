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
            ->getQuery()
            ->getResult();
    }

    /**
     * findEventsDQL
     *
     * @param \DateTime $from
     * @param \DateTime $to
     * @param int $type
     * @access public
     * @return Doctrine DQL
     */
    public function findEventsDQL($from, $to = NULL, $type = false)
    {
        $query = $this->_em->createQueryBuilder()
            ->add('select', 'e')
            ->add('from', 'ApplicationEventBundle:Event e');

        if( $from ){
            $query->andWhere('e.date_start > :date')->setParameter('date', $from->format('Y-m-d'))
                  ->add('orderBy', 'e.featured DESC, e.date_start ASC');
        }else if( $to ){
            $query->andWhere('e.date_start < :date')->setParameter('date', $to->format('Y-m-d'))
                  ->add('orderBy', 'e.date_start DESC');
        }
        
        if( is_numeric( $type ) ){
	        $query->andWhere('e.type = :type')->setParameter('type', $type);
        }

        return $query;
    }

    /**
     * findEventCities
     *
     * @param \DateTime $date
     * @param int $max
     * @param int $type
     * @access public
     * @return Doctrine\Common\ArrayCollection
     */
    public function findEventCities(\DateTime $date, $max=13, $type = false)
    {
        $query = $this->_em->createQueryBuilder()
            ->add('select', 'COUNT(e.id) AS total, c.name, c.id')
            ->add('from', 'ApplicationEventBundle:Event e, ApplicationCityBundle:City c')
            ->andWhere('e.city_id = c.id')
            ->andWhere('e.date_start > :date')->setParameter('date', $date->format('Y-m-d'))
            ->add('groupBy', 'c.id')
            ->add('orderBy', 'total DESC');
            
        if( is_numeric( $type ) ){
	        $query->andWhere('e.type = :type')->setParameter('type', $type);
        }
            
        $query->setMaxResults($max)
            ->getQuery()
            ->getResult();
            
        return $query;
    }

    /**
     * findEventsByCityDQL
     *
     * @param \DateTime $date
     * @param \Application\CityBundle\Entity\City $city
     * @param int $type
     * @access public
     * @return Doctrine DQL
     */
    public function findEventsByCityDQL(\DateTime $date, \Application\CityBundle\Entity\City $city, $type = false)
    {
        $query = $this->_em->createQueryBuilder()
            ->add('select', 'e')
            ->add('from', 'ApplicationEventBundle:Event e')
            ->andWhere('e.date_start > :date')->setParameter('date', $date->format('Y-m-d'))
            ->andWhere('e.city_id = :city_id')->setParameter('city_id', $city->getId());
        
        if( is_numeric( $type ) ){
	        $query->andWhere('e.type = :type')->setParameter('type', $type);
        }
            
        $query->add('orderBy', 'e.featured DESC, e.date_start ASC');
        return $query;
    }

}
