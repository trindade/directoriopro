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

    public function onKernelRequest(GetResponseEvent $event)
    {

        $request = $event->getRequest();
        $session = $request->getSession();

        $cookie_login = $request->cookies->get('login');
        if (!$session->get('id') && $cookie_login ) {
            $cookie_login_info = explode(':',$cookie_login);
            $user = $em->getRepository('ApplicationUserBundle:User')->find($cookie_login_info[0]);

            $pass = $user->getPass();
            if (!$pass) $pass = md5( $user->getDate()->format('Y-m-d H:i:s') );


            if ( $cookie_login_info[1] == $pass ) {
                $session = $this->getRequest()->getSession();
                $session->set('id', $user->getId());
                $session->set('name', $user->getShortName());
                $session->set('slug', $user->getSlug());
                $session->set('admin', $user->getAdmin());
            }
        }
    }
}
