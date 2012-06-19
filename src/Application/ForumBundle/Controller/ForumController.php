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

// reply, thread_show
use Application\ForumBundle\Entity\Reply;
use Application\ForumBundle\Form\ReplyType;

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
      $filter = $request->query->get('filter');
      if( $filter != 'noreply' ) $filter = 'latest';

      //$query = $em->getRepository('ApplicationAnunciosBundle:Post')->getPostsDQL($category_id);


    $query = $em->createQueryBuilder();
    $query->add('select', 't')
       ->add('from', 'ApplicationForumBundle:Thread t')
       ->add('orderBy', 't.featured DESC, t.date_update DESC');


    // categoria?
    //$category_id = $request->query->get('c');
    if ( $category_id ) {
       $query->andWhere('t.category_id = :category_id')->setParameter('category_id', $category_id);

    }

    if ( $filter == 'noreply' ) {
       $query->andWhere('t.replies = 0');
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



        return array('pager' => $html, 'entities' => $entities, 'forums' => $forums, 'filter' => $filter );


    }

    /**
     * Finds and displays a Forum entity.
     *
     * @Route("/f{id}/", name="forum_show")
     * @Template()
     */
    public function showAction($id)
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
           ->add('orderBy', 't.featured DESC, t.date_update DESC');

        
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

            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('forum_show', array('id' => $entity->getId())));
            
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

            return $this->redirect($this->generateUrl('forum_show', array('id' => $id)));
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


    /**
     * Finds and displays a Thread entity.
     *
     * @Route("/f{forum_id}/{slug}-{id}/", requirements={"forum_id" = "^\d+$", "slug" = "[a-z0-9\-]+", "id" = "^\d+$"}, name="thread_show")
     * @Template("ApplicationForumBundle:Thread:show.html.twig")
     */
    public function threadShowAction($forum_id, $slug, $id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ApplicationForumBundle:Thread')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Thread entity.');
        }

        $query = $em->createQueryBuilder();
        $query->add('select', 'r')
           ->add('from', 'ApplicationForumBundle:Reply r')
           ->andWhere('r.thread_id = :id')->setParameter('id', $id)
           ->add('orderBy', 'r.id ASC');
        $replies = $query->getQuery()->getResult();

        // obtener usuarios
        $total = count( $replies );
        for( $i = 0; $i < $total; $i++ ){
            $user_id = $replies[$i]->getUserId();
            $replies[$i]->user = $em->getRepository('ApplicationUserBundle:User')->find( $user_id );
        }

        
        // forum
        $forum = $em->getRepository('ApplicationForumBundle:Forum')->find($entity->getForumId());

        // form
        $reply = new Reply();
        $reply->setThreadId($id);
        $form  = $this->createForm(new ReplyType(), $reply);

        // es diferente usuario, visitas + 1
        $session = $this->getRequest()->getSession();
        $session_id = $session->get('id');
        if ( $session_id != $entity->getUserId() ) {
          $entity->setVisits($entity->getVisits() + 1 );
          $em->persist($entity);
          $em->flush();
        }

        $user = $em->getRepository('ApplicationUserBundle:User')->find($entity->getUserId());

        return array(
            'entity' => $entity,
            'forum' => $forum,
            'replies' => $replies,
            'form'   => $form->createView(),
            'user' => $user
        );
    }

    /**
     * Search Thread entities.
     *
     * @Route("/search", name="forum_search")
     * @Template()
     */
    public function searchAction()
    {
        $request = $this->getRequest();
        $search = strip_tags( $request->query->get('q') );


        $em = $this->getDoctrine()->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->add('select', 't')
           ->add('from', 'ApplicationForumBundle:Thread t')
           ->add('orderBy', 't.featured DESC, t.date_update DESC');

        if ( $search ) $qb->andWhere("( t.body LIKE '%".$search."%' OR t.title LIKE '%".$search."%' )");

        $entities = $qb->getQuery()->getResult();


        


        return array('entities' => $entities, 'search' => $search);
    }

}
