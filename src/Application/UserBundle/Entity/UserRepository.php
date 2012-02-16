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
}
