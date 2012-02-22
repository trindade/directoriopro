<?php

namespace Application\PageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

/**
 * Page controller.
 *
 * @Route("/page")
 */
class PageController extends Controller
{
	
    /**
     * resources page
     *
     * @Route("/resources/", name="page_resources")
     * @Template()
     */
    public function resourcesAction()
    {
	   // esta logueado?
	    $session = $this->getRequest()->getSession();
	    $id = $session->get('id');
		$total = 0;
	
	    if ( $id ) {
	   	 	$em = $this->getDoctrine()->getEntityManager();
		    $entity = $em->getRepository('ApplicationUserBundle:User')->find($id);

		    $query = "SELECT COUNT(u.id) AS total FROM User u WHERE u.ref_id = " . $id;
		    $db = $this->get('database_connection');
		    $result = $db->query($query)->fetch();
		    $total = 3 - $result['total'];
		}

	    return array('total' => $total);
	}
	
    /**
     * static page
     *
     * @Route("/{id}/", name="page_static")
     */
    public function staticAction($id)
    {
        $respuesta = $this->render('ApplicationPageBundle:Page:'.$id.'.html.twig');
        return $respuesta;
	}
}