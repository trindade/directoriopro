<?php

namespace Application\AnunciosBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * PostRepository
 *
 * @uses EntityRepository
 * @package AnunciosBundle
 * @subpackage Entity
 * @author Eduardo Gulias <me@egulias.com>
 */
class PostRepository extends EntityRepository
{
    /**
     * getPostsDQL
     *
     * @param int $category_id
     * @access public
     * @return Doctrine\ORM\QueryBuidler
     */
    public function getPostsDQL($category_id = 0)
    {
        $query = $this->_em->createQueryBuilder();
        $query->add('select', 'p')
           ->add('from', 'ApplicationAnunciosBundle:Post p')
           ->add('where', 'p.visible = 1')
           ->add('orderBy', 'p.featured DESC, p.id DESC');

        // categoria?
        if ( $category_id ) {
           $query->add('where', 'p.category_id = :category_id')->setParameter('category_id', $category_id);
        }

        return $query;
    }

    /**
     * getCityPostsDQL
     *
     * @param int $category_id
     * @param int $city_id
     * @access public
     * @return Doctrine\ORM\QueryBuidler
     */
    public function getCityPostsDQL($category_id = 0, $city_id)
    {
        $query = $em->createQueryBuilder();
        $query->add('select', 'p')
            ->add('from', 'ApplicationAnunciosBundle:Post p')
            ->andWhere('p.city_id = :city_id')->setParameter('city_id', intval($city_id))
            ->andWhere('p.visible = 1')
            ->add('orderBy', 'p.featured DESC, p.id DESC');

        // categoria?
        if ( $category_id ) {
            $query->add('where', 'p.category_id = :category_id')->setParameter('category_id', $category_id);

        }
    }

    /**
     * search
     *
     * @param mixed $search
     * @param int $category_id
     * @param mixed $location
     * @param nt $type
     * @access public
     * @return Doctrine\Common\ArrayCollection
     */
    public function search($search, $category_id, $location, $type)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->add('select', 'p')
          ->add('from', 'ApplicationAnunciosBundle:Post p')
          ->add('where', 'p.visible = 1')
          ->add('orderBy', 'p.featured DESC, p.id DESC');

        if ( $search ) $qb->andWhere("( p.body LIKE '%".$search."%' OR p.title LIKE '%".$search."%' )");
        if ( $category_id ) $qb->andWhere('p.category_id = :category_id')->setParameter('category_id', $category_id);
        if ( $location ) $qb->andWhere("p.location = :location")->setParameter('location', $location);
        if ( $type ) $qb->andWhere('p.type = :type')->setParameter('type', $type);

        return $qb->getQuery()->getResult();
    }


    /**
     * findVisible
     *
     * @param int $max
     * @access public
     * @return Doctrine\Common\ArrayCollection
     */
    public function findVisible($max = 20)
    {

        return $this->em->createQueryBuilder()
           ->add('select', 'p')
           ->add('from', 'ApplicationAnunciosBundle:Post p')
           ->add('where', 'p.visible = 1')
           ->add('orderBy', 'p.id DESC')
           ->setMaxResults($max)->getQuery()->getResult();
    }

}
