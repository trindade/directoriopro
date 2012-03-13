<?php

namespace Application\ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Application\ForumBundle\Entity\Thread;
use Application\ForumBundle\Form\ThreadType;

// reply
use Application\ForumBundle\Entity\Reply;
use Application\ForumBundle\Form\ReplyType;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\View\DefaultView;
use Pagerfanta\Adapter\DoctrineORMAdapter;

use Application\ApiBundle\Util\Util;

/**
 * Thread controller.
 *
 * @Route("/thread")
 */
class ThreadController extends Controller
{
    /**
     * Lists all Thread entities.
     *
     * @Route("/", name="thread")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('ApplicationForumBundle:Thread')->findAll();

        return array('entities' => $entities);
    }

    /**
     * Finds and displays a Thread entity.
     *
     * @Route("/{slug}-{id}/", requirements={"slug" = "[a-z0-9\-]+", "id" = "^\d+$"}, name="thread_show")
     * @Template()
     */
    public function showAction($slug, $id)
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
     * Displays a form to create a new Thread entity.
     *
     * @Route("/new", name="thread_new")
     * @Template()
     */
    public function newAction()
    {
        $session = $this->getRequest()->getSession();
        $session_id = $session->get('id');
        if ( !$session_id ) {
            return $this->redirect($this->generateUrl('user_welcome', array('back' => $_SERVER['REQUEST_URI'])));
        }

        $request = $this->getRequest();
        $id = $request->query->get('id');


        $entity = new Thread();
        $entity->setForumId( $id );

        $form   = $this->createForm(new ThreadType(), $entity);

        $em = $this->getDoctrine()->getEntityManager();
        $forums = $em->getRepository('ApplicationForumBundle:Forum')->findAll();

        return array(
            'entity' => $entity,
            'forums' => $forums,
            'form'   => $form->createView(),
            'id'     => $id
        );
    }

    /**
     * Creates a new Thread entity.
     *
     * @Route("/create", name="thread_create")
     * @Method("post")
     * @Template("ApplicationForumBundle:Thread:new.html.twig")
     */
    public function createAction()
    {
        $entity  = new Thread();
        $request = $this->getRequest();
        $form    = $this->createForm(new ThreadType(), $entity);
        $form->bindRequest($request);

        // rellenar campos que faltan
        $session = $this->getRequest()->getSession();
        $user_id = $session->get('id');
        $entity->setUserId( $user_id );
        $entity->setDate( new \DateTime("now") );

        

        if ($form->isValid()) {

            $slug = $entity->getTitle();
            $entity->setSlug(Util::slugify($slug));

            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            // contar forum total threads
            $forum = $em->getRepository('ApplicationForumBundle:Forum')->find( $entity->getForumId() );
            $forum->setThreads($forum->getThreads() + 1 );
            $em->persist($forum);
            $em->flush();


            return $this->redirect($this->generateUrl('thread_show', array('id' => $entity->getId(), 'slug' => $entity->getSlug())));
            
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing Thread entity.
     *
     * @Route("/{id}/edit", name="thread_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ApplicationForumBundle:Thread')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Thread entity.');
        }

        $editForm = $this->createForm(new ThreadType(), $entity);
        

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            
        );
    }

    /**
     * Edits an existing Thread entity.
     *
     * @Route("/{id}/update", name="thread_update")
     * @Method("post")
     * @Template("ApplicationForumBundle:Thread:edit.html.twig")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ApplicationForumBundle:Thread')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Thread entity.');
        }


        $session = $this->getRequest()->getSession();
        $user_id = $session->get('id');
        $admin = $session->get('admin');

        if ( ( $entity->getUserId() == $user_id ) || $admin ) {

            $editForm   = $this->createForm(new ThreadType(), $entity);
            

            $request = $this->getRequest();

            $editForm->bindRequest($request);

            if ($editForm->isValid()) {

                $slug = $entity->getTitle();
                $entity->setSlug(Util::slugify($slug));

                $em->persist($entity);
                $em->flush();

                return $this->redirect($this->generateUrl('thread_show', array('id' => $id, 'slug' => $entity->getSlug())));
            }

            return array(
                'entity'      => $entity,
                'edit_form'   => $editForm->createView(),
                
            );
        }else{

            $url = $this->generateUrl('thread_show', array('id' => $entity->getId(), 'slug' => $entity->getSlug()));
            return $this->redirect($url);
        }
    }

    /**
     * Deletes a Thread entity.
     *
     * @Route("/{id}/delete", name="thread_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('ApplicationForumBundle:Thread')->find($id);
        if (!$entity) {
          throw $this->createNotFoundException('Unable to find Post entity.');
        }

        $forum = $em->getRepository('ApplicationForumBundle:Forum')->find($entity->getForumId());

        $session = $this->getRequest()->getSession();
        $user_id = $session->get('id');
        $admin = $session->get('admin');

        if ( ( $entity->getUserId() == $user_id ) || $admin ) {

            $session = $this->getRequest()->getSession();
            $user_id = $session->get('id');
            $admin = $session->get('admin');

            if ( ( $entity->getUserId() == $user_id ) || $admin ) {

              $em->remove($entity);
              $em->flush();


              // contar forum total threads
              $forum->setThreads($forum->getThreads() - 1 );
              $em->persist($forum);
              $em->flush();


              // borrar respuestas
              $query = $em->createQueryBuilder();
              $query->add('select', 'r')
                 ->add('from', 'ApplicationForumBundle:Reply r')
                 ->andWhere('r.thread_id = :id')->setParameter('id', $id)
                 ->add('orderBy', 'r.id ASC');
              $replies = $query->getQuery()->getResult();

              foreach( $replies as $reply ){
                  $em->remove($reply);
                  $em->flush(); 
              }



            }

        }
        $url = $this->generateUrl('forum_show', array('id' => $forum->getId(), 'slug' => $forum->getSlug()));
        return $this->redirect($url);
    }


    /**
     * Admin Thread entities.
     *
     * @Route("/admin", name="thread_admin")
     * @Template()
     */
    public function adminAction()
    {

        $session = $this->getRequest()->getSession();
        if ( !$session->get('admin') ) {
          return $this->redirect('/');
        }


        $request = $this->getRequest();
        $page = $request->query->get('page');
        if ( !$page ) $page = 1;

            $em = $this->getDoctrine()->getEntityManager();




        $query = $em->createQueryBuilder();
        $query->add('select', 't')
           ->add('from', 'ApplicationForumBundle:Thread t')
           ->add('orderBy', 't.featured DESC, t.id DESC');

        // categoria?
        $category_id = $request->query->get('c');
        if ( $category_id ) {
           $query->add('where', 't.forum_id = :forum_id')->setParameter('forum_id', $category_id);
        }




        $adapter = new DoctrineORMAdapter($query);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(20); // 10 by default
        $maxPerPage = $pagerfanta->getMaxPerPage();

        $pagerfanta->setCurrentPage($page); // 1 by default
        $entities = $pagerfanta->getCurrentPageResults();
        $routeGenerator = function($page,$category_id) {
          $url = '?page='.$page;
          if ( $category_id ) $url .= '&c=' . $category_id;
            return $url;
        };
        $view = new DefaultView();
        $html = $view->render($pagerfanta, $routeGenerator, array('category_id' => (int)$category_id));

        // estadisticas de anuncios
        $query = "SELECT COUNT(t.id) AS total, t.forum_id, f.title FROM Thread t, Forum f WHERE t.forum_id = f.id GROUP BY f.title ORDER BY total DESC";
        $db = $this->get('database_connection');
        $categories = $db->fetchAll($query);

        return array('categories_aux' => $categories, 'pager' => $html, 'entities' => $entities);
    }

    /**
     * Feature Thread entities.
     *
     * @Route("/admin/featured/{id}/{value}", name="thread_admin_featured")
     * @Template()
     */
    public function featuredAction($id,$value)
    {
      $session = $this->getRequest()->getSession();
      if ( !$session->get('admin') ) {
        return $this->redirect('/');
      }

      // existe thread?
      $em = $this->getDoctrine()->getEntityManager();
      $entity = $em->getRepository('ApplicationForumBundle:Thread')->find($id);

      if (!$entity) {
          throw $this->createNotFoundException('Unable to find User entity.');
      }

      $entity->setFeatured($value);
      $em->persist($entity);
      $em->flush();

      return $this->redirect( $_SERVER['HTTP_REFERER'] );
    }



    /**
     * Search Thread entities.
     *
     * @Route("/search", name="thread_search")
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
           ->add('orderBy', 't.featured DESC, t.date DESC');

        if ( $search ) $qb->andWhere("( t.body LIKE '%".$search."%' OR t.title LIKE '%".$search."%' )");

        $entities = $qb->getQuery()->getResult();


        


        return array('entities' => $entities, 'search' => $search);
    }

}
