<?php

namespace Application\TagBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Application\TagBundle\Entity\Tag;
use Application\TagBundle\Entity\TagUser;

use Application\ApiBundle\Util\Util;


/**
 * Tag controller.
 *
 * @Route("/tag")
 */
class TagController extends Controller
{
    /**
     * Lists all Tag entities.
     *
     * @Route("/", name="tag")
     * @Template()
     */
    public function indexAction()
    {

        $em = $this->getDoctrine()->getEntityManager();
        
	    $query = $em->createQueryBuilder();
	    $query->add('select', 't')
	       ->add('from', 'ApplicationTagBundle:Tag t')
	       ->add('orderBy', 't.users DESC, t.title ASC');
	       
	    $entities = $query->getQuery()->getResult();

        return array('entities' => $entities);
    }

    /**
     * Show tag
     *
     * @Route("/{slug}/show", name="tag_show")
     * @Template()
     */
    public function showAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ApplicationTagBundle:Tag')->find( array('slug'=>$slug) );

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Test entity.');
        }
        
        // get users

        return array(
            'entity' => $entity
		);
    }

    /**
     * Creates a new Tag entity.
     *
     * @Route("/create", name="tag_create")
     * @Method("post")
     */
    public function createAction()
    {
    	$request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();
        
        // usuario
		$session = $this->getRequest()->getSession();
		$user_id = $session->get('id');
		
		// existe tag?
		$entity = $em->getRepository('ApplicationTagBundle:Tag')->find( array('slug'=>$id) );
		
		if( !$entity ){
			
			// crear tag
			$title = $request->request->get('title');
	        $entity  = new Tag();
			$entity->setUserId( $user_id );
			$entity->setTitle( $title );
			$entity->setSlug( Util::slugify( $title ) );
			$entity->setDate( new \DateTime("now") );
			
			$users = 1;
			
		}else{
			
			// actualizar
			$users = $entity->getUsers() + 1;
		}
		
		$entity->setUsers( $users );
        $em->persist($entity);
        $em->flush();
        
        
        // vincular usuario
        $link  = new TagUser();
		$link->setTagId( $entity->getId() );
		$link->setUserId( $user_id );
		$link->setDate( new \DateTime("now") );
		
        $em->persist($link);
        $em->flush();
        
        return array('entity' => $entity);
    }

    /**
     * Deletes a Tag entity.
     *
     * @Route("/{id}/delete", name="tag_delete")
     */
    public function deleteAction($id)
    {
    
        $session = $this->getRequest()->getSession();
        $admin = $session->get('admin');
        
        // es admin?
        if( !$admin ) return $this->redirect('/');
    
        // existe?
		$em = $this->getDoctrine()->getEntityManager();
		$entity = $em->getRepository('ApplicationTagBundle:Tag')->find($id);
		if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }
		
        $em->remove($entity);
        $em->flush();
        
        //return $this->redirect( $this->generateUrl('tag') );
        die(1);
    }


    /**
     * Tag link
     *
     * @Route("/link", name="tag_link")
     */
    public function linkAction()
    {
        // usuario
		$session = $this->getRequest()->getSession();
		$user_id = $session->get('id');
		if( !$user_id ) die();
    
        $request = $this->getRequest();
        $tag_id = $request->query->get('tag_id');
        $action = $request->query->get('action');
		
		// existe tag?
		$em = $this->getDoctrine()->getEntityManager();
		$entity = $em->getRepository('ApplicationTagBundle:Tag')->find( array('slug'=>$id) );
		if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tag entity.');
        }
		
		// calcular total usuarios
		$users = $entity->getUsers();
		
		// existe vinculo?
		$link = $em->getRepository('ApplicationTagBundle:TagUser')->find( array('tag_id'=>$entity, 'user_id' => $user_id) );
		
		// vincular
		if( $action == 1 ){
			
			// si no existe crear vinculo
			if( !$link ){
		        $link  = new TagUser();
				$link->setTagId( $entity->getId() );
				$link->setUserId( $user_id );
				$link->setDate( new \DateTime("now") );
		        $em->persist($link);
		        $users++;
			}
			
		}else{

			$em->remove($link);
	        $users--;
			
		}
        
        $entity->setUsers( $users );
        $em->persist($entity);
		
		$em->flush();

        die($action);
    }

}
