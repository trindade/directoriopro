<?php

namespace Application\TagBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Application\TagBundle\Entity\Tag;
use Application\TagBundle\Entity\TagUser;

use Application\ApiBundle\Util\Util;
use Symfony\Component\HttpFoundation\Response;

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
            throw $this->createNotFoundException('Unable to find Tag entity.');
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
     */
    public function createAction()
    {
    	$request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();
        
        // usuario
		$session = $this->getRequest()->getSession();
		$user_id = $session->get('id');
		if( !$user_id ) return new Response('error');
		
		// existe tag?
		$title = trim( $request->query->get('title') );
		$entity = $em->getRepository('ApplicationTagBundle:Tag')->findOneBy( array('title'=>$title) );
		
		if( !$entity ){
			
			// crear tag
	        $entity  = new Tag();
			$entity->setUserId( $user_id );
			$entity->setTitle( $title );
			$entity->setSlug( Util::slugify( $title ) );
			$entity->setDate( new \DateTime("now") );
			
			//$entity->setUsers(1);
			
		}
		
        $em->persist($entity);
        $em->flush();
        
        
        // existe vinculo
        $link = $em->getRepository('ApplicationTagBundle:TagUser')->findOneBy( array('tag_id' => $entity->getId(), 'user_id' => $user_id) );
        
        if( !$link ){
        
	        // vincular usuario
	        $link  = new TagUser();
			$link->setTagId( $entity->getId() );
			$link->setUserId( $user_id );
			$link->setDate( new \DateTime("now") );
			
	        $em->persist($link);
	        $em->flush();
	        
	        
	        // recalcular total
		    $query = "SELECT COUNT(id) AS total FROM TagUser WHERE tag_id = " . $entity->getId();
		    $db = $this->get('database_connection');
		    $result = $db->query($query)->fetch();
		    $total = $result['total'];
		    
		    $entity->setUsers( $total );
	        $em->persist($entity);
	        $em->flush();
        
        }
        
        //return array('entity' => $entity);
        return new Response('1');
    }


    /**
     * Tag link
     *
     * @Route("/follow", name="tag_follow")
     */
    public function followAction()
    {
    	return new Response('test');
    	
    	/*
        // usuario
		$session = $this->getRequest()->getSession();
		$user_id = $session->get('id');
		if( !$user_id ) return new Response('error');
    
        $request = $this->getRequest();
        $title = $request->query->get('title');
        $action = $request->query->get('action');
		
		// existe tag?
		$em = $this->getDoctrine()->getEntityManager();
		$entity = $em->getRepository('ApplicationTagBundle:Tag')->find( array('title'=>$title) );
		if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tag entity.');
        }
		
		// existe vinculo?
		$link = $em->getRepository('ApplicationTagBundle:TagUser')->findOneBy( array('tag_id' => $entity->getId(), 'user_id' => $user_id) );
		
		// vincular
		if( $action == 1 ){
			
			// si no existe crear vinculo
			if( !$link ){
		        $link  = new TagUser();
				$link->setTagId( $entity->getId() );
				$link->setUserId( $user_id );
				$link->setDate( new \DateTime("now") );
		        $em->persist($link);
			}
			
		}else{

			$em->remove($link);
			
		}
		
		
        // recalcular total
	    $query = "SELECT COUNT(id) AS total FROM TagUser WHERE tag_id = " . $entity->getId();
	    $db = $this->get('database_connection');
	    $result = $db->query($query)->fetch();
	    $total = $result['total'];
		
		if( $total ){
			$entity->setUsers( $total );
			$em->persist($entity);
		}else{
			$em->remove($entity);
		}

		$em->flush();

        return new Response($action);
        */
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

        
        
        // eliminar resultados
          // borrar respuestas
          $query = $em->createQueryBuilder();
          $query->add('select', 'u')
             ->add('from', 'ApplicationTagBundle:TagUser u')
             ->andWhere('u.tag_id = :id')->setParameter('id', $id)
             ->add('orderBy', 'u.id ASC');
          $replies = $query->getQuery()->getResult();

          foreach( $replies as $reply ){
              $em->remove($reply);
          }
        
        $em->flush();
        
        //return $this->redirect( $this->generateUrl('tag') );
        return new Response('1');
    }
    
    /**
     * autocomplete
     *
     * @Route("/autocomplete", name="tag_autocomplete")
     * @Template()
     */
    public function autocompleteAction()
    {
	    $request = $this->getRequest();
	    $q = $request->query->get('term');
	
	    $em = $this->getDoctrine()->getEntityManager();
	    $query = $em->createQuery("SELECT t.id, t.title AS label, t.title AS value FROM ApplicationTagBundle:Tag t WHERE t.title LIKE '" . addslashes( $q ) . "%' ORDER BY t.users DESC, t.title ASC");
	    $results = $query->setMaxResults(5)->getResult();
	
	    return array('result' => json_encode($results));
	}

}
