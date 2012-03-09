<?php

namespace Application\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * UserRepository
 *
 * @uses EntityRespository
 * @package UserBundle
 * @subpackage Entity
 * @author Eduardo Gulias <me@egulias.com>
 */
class UserRepository extends EntityRepository
{
    /**
     * findByEmailAndPsw
     *
     * @param string $email
     * @param string $psw
     * @access public
     * @return Application\UserBundle\Entity\User
     */
    public function findByEmailAndPsw($email, $pass)
    {

        $query = $this->_em->createQuery("SELECT u FROM ApplicationUserBundle:User u
            WHERE u.email = :email AND u.pass = :pass");
        $query->setParameters(array(
            'email' => $email,
            'pass' => $pass
        ));
        return $query->getSingleResult();
    }

    /**
     * findUsersByCity
     *
     * @param \Application\CityBundle\Entity\City $city
     * @param int $max
     * @access public
     * @return \Doctrine\Common\ArrayCollection
     */
    public function findUsersByCity(\Application\CityBundle\Entity\City $city, $max = 10)
    {
        return $this->_em->createQueryBuilder()
            ->add('select', 'u')
            ->add('from', 'ApplicationUserBundle:User u')
            ->andWhere('u.city_id = :id')->setParameter('id', $city->getId())
            ->add('orderBy', 'u.date_login DESC')
            ->setMaxResults($max)
            ->getQuery()
            ->getResult();
    }
}
