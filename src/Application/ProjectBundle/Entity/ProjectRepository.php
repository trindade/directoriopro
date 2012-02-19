<?php

namespace Application\ProjectBundle\Entity;

use Doctrine\ORM\EntityRespository;

/**
 * ProjectRepository
 *
 * @uses EntityRepository
 * @package ProjectBundle
 * @subpackage Entity
 * @author Eduardo Gulias <me@egulias.com>
 */
class ProjectRepository extends EntityRepository
{

    /**
     * getProjectsDQL
     *
     * @param int $type
     * @param int $category_id
     * @access public
     * @return Doctrine\ORM\QueryBuilder
     */
    public function getProjectsDQL($type = 0, $category_id = 0)
    {
        $query = $this->_em->createQueryBuilder();
        $query->add('select', 'p')
            ->add('from', 'ApplicationProjectBundle:Project p')
            ->add('orderBy', 'p.id DESC')
            ->andWhere('p.type = :type')->setParameter('type', $type);
        if ( $category_id ) $query->andWhere('p.category_id = :category_id')->setParameter('category_id', $category_id);
        return $query;
    }
}
