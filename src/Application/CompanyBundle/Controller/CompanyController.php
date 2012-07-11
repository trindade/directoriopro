<?php

namespace Application\CompanyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

/**
 * Company controller.
 *
 * @Route("/company")
 */
class CompanyController extends Controller
{
	
    /**
     * static company
     *
     * @Route("/{id}/", name="company_static")
     */
    public function staticAction($id)
    {
        return $this->render('ApplicationCompanyBundle:Company:'.$id.'.html.twig');
	}
}