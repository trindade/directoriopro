<?php

namespace Application\ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Application\ForumBundle\Entity\Forum;
use Application\ForumBundle\Form\ForumType;

// thread
use Application\ForumBundle\Entity\Thread;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\View\DefaultView;
use Pagerfanta\Adapter\DoctrineORMAdapter;

use Application\ApiBundle\Util\Util;

/**
 * Forum controller.
 *
 * @Route("/forum")
 */
class ForumController extends Controller
{
    /**
     * Lists all Forum entities.
     *
     * @Route("/", name="forum")
     * @Template()
     */
    public function indexAction()
    {
      $request = $this->getRequest();
      $em = $this->getDoctrine()->getEntityManager();

      $category_id = $request->query->get('c',0);
      $page = $request->query->get('page',1);

      //$query = $em->getRepository('ApplicationAnunciosBundle:Post')->getPostsDQL($category_id);


    $query = $em->createQueryBuilder();
    $query->add('select', 't')
       ->add('from', 'ApplicationForumBundle:Thread t')
       ->add('orderBy', 't.featured DESC, t.id DESC');


    // categoria?
    //$category_id = $request->query->get('c');
    if ( $category_id ) {
       $query->add('where', 't.category_id = :category_id')->setParameter('category_id', $category_id);

    }


      $adapter = new DoctrineORMAdapter($query);

      $pagerfanta = new Pagerfanta($adapter);
      $pagerfanta->setMaxPerPage(10); // 10 by default
      $maxPerPage = $pagerfanta->getMaxPerPage();

      $pagerfanta->setCurrentPage($page); // 1 by default
      $entities = $pagerfanta->getCurrentPageResults();
      $routeGenerator = function($page, $category_id) {
        $url = '?page='.$page;
        if ( $category_id ) $url .= '&c=' . $category_id;
        return $url;
      };

      $view = new DefaultView();
      $html = $view->render($pagerfanta, $routeGenerator, array('category_id' => (int)$category_id));


      // forums
      $forums = $em->getRepository('ApplicationForumBundle:Forum')->findAll();


      return array('pager' => $html, 'entities' => $entities, 'forums' => $forums );


    }

    /**
     * Finds and displays a Forum entity.
     *
     * @Route("/{slug}-{id}/", requirements={"slug" = "[a-z0-9\-]+", "id" = "^\d+$"}, name="forum_show")
     * @Template()
     */
    public function showAction($slug, $id)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ApplicationForumBundle:Forum')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Forum entity.');
        }

        


        $page = $request->query->get('page');
        if ( !$page ) $page = 1;



        $query = $em->createQueryBuilder();
        $query->add('select', 't')
           ->add('from', 'ApplicationForumBundle:Thread t')
           ->add('where', 't.forum_id = :forum_id')->setParameter('forum_id', $id)
           ->add('orderBy', 't.date DESC'); //date_edit

        
        $adapter = new DoctrineORMAdapter($query);

        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(10); // 10 by default
        $maxPerPage = $pagerfanta->getMaxPerPage();

        $pagerfanta->setCurrentPage($page); // 1 by default
        $threads = $pagerfanta->getCurrentPageResults();
        $routeGenerator = function($page, $id) {
          $url = '?page='.$page;
          if ( $id ) $url .= '&c=' . $id;
            return $url;
        };
        $view = new DefaultView();
        $html = $view->render($pagerfanta, $routeGenerator, array('category_id' => $id));







        


        return array(
            'entity'      => $entity,
            'threads'     => $threads,
            'pager'       => $html, 
            );
    }

    /**
     * Displays a form to create a new Forum entity.
     *
     * @Route("/new", name="forum_new")
     * @Template()
     */
    public function newAction()
    {
        // es admin ?
        $session = $this->getRequest()->getSession();
        $admin = $session->get('admin');
        if( !$admin ){
            return $this->redirect( $this->generateUrl('forum') );
        }


        $entity = new Forum();
        $form   = $this->createForm(new ForumType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Creates a new Forum entity.
     *
     * @Route("/create", name="forum_create")
     * @Method("post")
     * @Template("ApplicationForumBundle:Forum:new.html.twig")
     */
    public function createAction()
    {
        // es admin ?
        $session = $this->getRequest()->getSession();
        $admin = $session->get('admin');
        if( !$admin ){
            return $this->redirect( $this->generateUrl('forum') );
        }

        $entity  = new Forum();
        $request = $this->getRequest();
        $form    = $this->createForm(new ForumType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {

            $slug = $entity->getTitle();
            $entity->setSlug(Util::slugify($slug));

            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('forum_show', array('id' => $entity->getId(), 'slug' => $entity->getSlug())));
            
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing Forum entity.
     *
     * @Route("/{id}/edit", name="forum_edit")
     * @Template()
     */
    public function editAction($id)
    {
        // es admin ?
        $session = $this->getRequest()->getSession();
        $admin = $session->get('admin');
        if( !$admin ){
            return $this->redirect( $this->generateUrl('forum') );
        }


        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ApplicationForumBundle:Forum')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Forum entity.');
        }

        $editForm = $this->createForm(new ForumType(), $entity);
        

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        );
    }

    /**
     * Edits an existing Forum entity.
     *
     * @Route("/{id}/update", name="forum_update")
     * @Method("post")
     * @Template("ApplicationForumBundle:Forum:edit.html.twig")
     */
    public function updateAction($id)
    {
        // es admin ?
        $session = $this->getRequest()->getSession();
        $admin = $session->get('admin');
        if( !$admin ){
            return $this->redirect( $this->generateUrl('forum') );
        }

        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ApplicationForumBundle:Forum')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Forum entity.');
        }

        $editForm   = $this->createForm(new ForumType(), $entity);
        

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('forum_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        );
    }

    /**
     * Deletes a Forum entity.
     *
     * @Route("/{id}/delete", name="forum_delete")
     */
    public function deleteAction($id)
    {
        // es admin ?
        $session = $this->getRequest()->getSession();
        $admin = $session->get('admin');
        if( $admin ){
            
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('ApplicationForumBundle:Forum')->find($id);
            if (!$entity) {
              throw $this->createNotFoundException('Unable to find Post entity.');
            }

            $session = $this->getRequest()->getSession();
            $user_id = $session->get('id');
            $admin = $session->get('admin');

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect( $this->generateUrl('forum') );

    }

}
