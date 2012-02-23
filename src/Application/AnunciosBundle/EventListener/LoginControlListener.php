<?php

namespace Application\AnunciosBundle\EventListener;


use
    Symfony\Component\HttpKernel\Event\GetResponseEvent,
    Symfony\Component\HttpKernel\HttpKernelInterface
;

/**
 * LoginControlListener
 *
 * @package AnunciosBundle
 * @subpackage EventListener
 * @author Eduardo Gulias Davis <me@egulias.com>
 */
class LoginControlListener
{

    /**
     * em
     *
     * @var Doctrine\ORM\EntityManager
     * @access private
     */
    private $em = null;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $session = $request->getSession();

        $cookie_login = $request->cookies->get('login');
        if (!$session->get('id') && $cookie_login ) {
            $cookie_login_info = explode(':',$cookie_login);
            $user = $this->em->getRepository('ApplicationUserBundle:User')->find($cookie_login_info[0]);

            if (!$pass = $user->getPass()) $pass = md5( $user->getDate()->format('Y-m-d H:i:s') );

            if ($cookie_login_info[1] == $pass ) {
                $session->set('id', $user->getId());
                $session->set('name', $user->getShortName());
                $session->set('slug', $user->getSlug());
                $session->set('admin', $user->getAdmin());
            }
        }
    }
}
