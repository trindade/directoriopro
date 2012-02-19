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