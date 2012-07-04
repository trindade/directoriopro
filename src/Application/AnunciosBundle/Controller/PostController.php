<?php

namespace Application\AnunciosBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Application\AnunciosBundle\Entity\Post;
use Application\AnunciosBundle\Entity\PostReply;
use Application\UserBundle\Entity\User;
use Application\AnunciosBundle\Entity\Contact;
use Application\AnunciosBundle\Form\PostType;
use Application\AnunciosBundle\Form\ContactType;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\View\DefaultView;
use Pagerfanta\Adapter\DoctrineORMAdapter;

use Application\ApiBundle\Util\Util;

define('CAT_OTHER',9);

/**
 * Post controller.
 *
 * @Route("/post")
 */
class PostController extends Controller
{
    /**
     * Lists all Post entities.
     *
     * @Route("/", name="post")
     * @Template()
     */
    public function indexAction()
    {
      $request = $this->getRequest();
      $em = $this->getDoctrine()->getEntityManager();

      $category_id = $request->query->get('c',0);
      $page = $request->query->get('page',1);

      $query = $em->getRepository('ApplicationAnunciosBundle:Post')->getPostsDQL($category_id);

      $adapter = new DoctrineORMAdapter($query);

      $pagerfanta = new Pagerfanta($adapter);
      $pagerfanta->setMaxPerPage(20); // 10 by default
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




      $home = (!$category_id && $page == 1);

      return array('pager' => $html, 'entities' => $entities, 'home' => $home );
    }

    /**
     * Lists all Posts entities by city.
     *
     * @Route("/city/{id}", name="post_city")
     * @Template()
     */
    public function cityAction($id)
    {
        $request = $this->getRequest();
        $page = $request->query->get('page',1);

        $em = $this->getDoctrine()->getEntityManager();

        $city = $em->getRepository('ApplicationCityBundle:City')->find($id);

        if (!$city) {
          throw $this->createNotFoundException('Unable to find Post entity.');
        }

        $country = current( $em->getRepository('ApplicationCityBundle:Country')->findBy(array('code' => $city->getCode())) );

        $category_id = $request->query->get('c',0);

        $query = $em->getRepository('ApplicationAnunciosBundle:Post')->getCityPostsDQL($category_id, $city->getId());

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

        return array('city' => $city, 'country' => $country, 'pager' => $html, 'entities' => $entities );
    }

    /**
     * Finds and displays a Post entity.
     *
     * @Route("/{id}/show", name="post_show2")
     * @Template()
     */
    public function show2Action($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('ApplicationAnunciosBundle:Post')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }
        return $this->redirect($this->generateUrl('post_show', array('id' => $entity->getID(),
          'slug' => $entity->getSlug() )),301);
    }

    /**
     * Displays a form to create a new Post entity.
     *
     * @Route("/new", name="post_new")
     * @Template()
     */
    public function newAction()
    {

        $session = $this->getRequest()->getSession();
        $session_id = $session->get('id');
        if ( !$session_id ) {
          return $this->redirect($this->generateUrl('user_welcome', array('back' => $_SERVER['REQUEST_URI'])));
        }

        //si no es post
        $request = $this->getRequest();

        if ($request->getMethod() != 'POST') {
            $em = $this->getDoctrine()->getEntityManager();
            $user = $em->getRepository('ApplicationUserBundle:User')->find($session_id);
            $email = $user->getEmail();
        }

        $type = $request->query->get('type') ? 1 : 0;


        $entity = new Post();
        $entity->setType($type);
        $entity->setEmail( $email );
        $form   = $this->createForm(new PostType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'type'   => $type
        );
    }

    /**
     * Creates a new Post entity.
     *
     * @Route("/create", name="post_create")
     * @Method("post")
     * @Template("ApplicationAnunciosBundle:Post:new.html.twig")
     */
    public function createAction()
    {
        $entity  = new Post();
        $request = $this->getRequest();
        $form    = $this->createForm(new PostType(), $entity);
        $form->bindRequest($request);

        // rellenar campos que faltan
        $session = $this->getRequest()->getSession();
        $user_id = $session->get('id');
        $entity->setUserId( $user_id );
        $entity->setDate( new \DateTime("now") );


        if ($form->isValid()) {

          $em = $this->getDoctrine()->getEntityManager();

          $slug = $entity->getTitle();
          $city_id = $entity->getCityId();
          if ( $city_id ) {
            $city = $em->getRepository('ApplicationCityBundle:City')->find( $city_id );
            $slug .= ' ' . $city->getName();
          }
          $company = $entity->getCompany();
          if ( $company ) {
            $slug .= ' ' . $company;
          }

          $entity->setSlug(Util::slugify($slug));
          
          // corregir descripcion
          $entity->setBody( strip_tags( $entity->getBody() ) );


          // bug corregir location
          $post = $form->getData();
          $this->fixLocation(&$post, &$entity, &$em);

          $em->persist($entity);
          $em->flush();

          return $this->redirect($this->generateUrl('post_show', array('id' => $entity->getId(),
            'slug' => $entity->getSlug())) . '#success');

        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/{id}/edit", name="post_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ApplicationAnunciosBundle:Post')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }

        $session = $this->getRequest()->getSession();
        $user_id = $session->get('id');
        $admin = $session->get('admin');

        if ( ( $entity->getUserId() == $user_id ) || $admin ) {

          $editForm = $this->createForm(new PostType(), $entity);

          return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
          );

        }else {
          $url = $this->generateUrl('post_show', array('id' => $entity->getId(), 'slug' => $entity->getSlug()));
          return $this->redirect($url);
        }
    }

    /**
     * Edits an existing Post entity.
     *
     * @Route("/{id}/update", name="post_update")
     * @Method("post")
     * @Template("ApplicationAnunciosBundle:Post:edit.html.twig")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ApplicationAnunciosBundle:Post')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }

        $location = $entity->getLocation();
        $session = $this->getRequest()->getSession();
        $user_id = $session->get('id');
        $admin = $session->get('admin');

        if ( ( $entity->getUserId() == $user_id ) || $admin ) {

          $editForm   = $this->createForm(new PostType(), $entity);

          $request = $this->getRequest();

          $editForm->bindRequest($request);

          if ($editForm->isValid()) {

            $slug = $entity->getTitle();
            $city_id = $entity->getCityId();
            if ( $city_id ) {
              $city = $em->getRepository('ApplicationCityBundle:City')->find( $city_id );
              $slug .= ' ' . $city->getName();
            }
            $company = $entity->getCompany();
            if ( $company ) {
              $slug .= ' ' . $company;
            }

            $entity->setSlug(Util::slugify($slug));

            // bug corregir location
            $post = $editForm->getData();
            if( $post->getLocation() != $location ){
              $this->fixLocation(&$post, &$entity, &$em);
            }
            
            // corregir descripcion
	        $entity->setBody( strip_tags( $entity->getBody() ) );

            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('post_show', array('id' => $id,
              'slug' => $entity->getSlug())));
          }

          return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
          );

        }else {
          $url = $this->generateUrl('post_show', array('id' => $entity->getId(), 'slug' => $entity->getSlug()));
          return $this->redirect($url);
        }
    }

    /**
     * Deletes a Post entity.
     *
     * @Route("/{id}/delete", name="post_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('ApplicationAnunciosBundle:Post')->find($id);
        if (!$entity) {
          throw $this->createNotFoundException('Unable to find Post entity.');
        }

        $session = $this->getRequest()->getSession();
        $user_id = $session->get('id');
        $admin = $session->get('admin');

        if ( ( $entity->getUserId() == $user_id ) || $admin ) {

          $em->remove($entity);
          
          
          // borrar respuestas
          $query = $em->createQueryBuilder();
          $query->add('select', 'r')
             ->add('from', 'ApplicationAnunciosBundle:PostReply r')
             ->andWhere('r.post_id = :id')->setParameter('id', $id)
             ->add('orderBy', 'r.id ASC');
          $replies = $query->getQuery()->getResult();

          foreach( $replies as $reply ){
              $em->remove($reply);
              //$em->flush(); 
          }
          
          
          
          $em->flush();

          $url = $this->generateUrl('post');
        }else {
          $url = $this->generateUrl('post_show', array('id' => $entity->getId(), 'slug' => $entity->getSlug()));

        }
        return $this->redirect($url);
    }

    /**
     * Search Post entities.
     *
     * @Route("/search", name="post_search")
     * @Template()
     */
    public function searchAction()
    {
        $request = $this->getRequest();
        $search = strip_tags( $request->query->get('q') );
        $category_id = $request->query->get('c');
        $type = (int)$request->query->get('t');
        $location = $request->query->get('location');

        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('ApplicationAnunciosBundle:Post')
          ->search($search, $category_id, $location, $type);

        return array('entities' => $entities, 'form_category' =>$category_id, 'form_type' => $type, 'search' => $search);
    }

    /**
     * Feed Post entities.
     *
     * @Route("/feed", name="post_feed", defaults={"_format"="xml"})
     * @Template()
     */
    public function feedAction()
    {

    $request = $this->getRequest();


    $em = $this->getDoctrine()->getEntityManager();

    $qb = $em->createQueryBuilder()
       ->add('select', 'p')
       ->add('from', 'ApplicationAnunciosBundle:Post p')
       ->add('where', 'p.visible = 1')
       ->add('orderBy', 'p.id DESC')
       ->setMaxResults(10);

    // categoria?
    $category_id = $request->query->get('c');
    if ( $category_id ) {
       $qb->andWhere('p.category_id = :category_id')->setParameter('category_id', $category_id);
    }

    

    $query = $qb->getQuery();
    $entities = $query->getResult();




        return array('entities' => $entities, 'form_category' =>$category_id);
    }

    /**
     * Contact form
     *
     * @Route("/{id}/contact", name="post_contact")
     * @Template()
     */
    public function contactAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
    $entity = $em->getRepository('ApplicationAnunciosBundle:Post')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

    $form = $this->createForm(new ContactType());
    $result = 'no';

    $request = $this->getRequest();
    if ($request->getMethod() == 'POST') {
          $form->bindRequest($request);




          if ($form->isValid()) {


        $values = $form->getData();

        $toEmail = $entity->getEmail();

        extract( $values );

        if ( filter_var($email, FILTER_VALIDATE_EMAIL) && !strstr( $body, '<a href=' ) ) {

          $user_id = $this->getRequest()->getSession()->get('id');


          
          

         


          $url = $this->generateUrl('post_replies', array('id' => $entity->getId()), true);
          $subject = 'Nuevo candidato oferta en betabeers';
          $mensaje = $name . ' se ha interesado por la oferta <a href="' . $url . '">Ver mensaje</a>';

          require __DIR__ . '/../../../../app/config/mailjet.php';
          $result = mailing($toEmail, $subject, $mensaje);







          // contabilizar contacto
          $entity->setInterested( $entity->getInterested() + 1 );
          $em->persist($entity);
          
          
          // add reply
          $reply = new PostReply();
          $reply->setPostId( $id );
          $reply->setUserId( $user_id );
          $reply->setBody( $body );
          $reply->setName( $name );
          $reply->setEmail( $email );
          $reply->setDate( new \DateTime("now") );          
          $reply->setLinkedin( $linkedin );
          $em->persist($reply);


          $em->flush();


        }else {
          return new Response("SPAM!");
        }






          }
      }

        return array(
        	'form' => $form->createView(),
            'entity'      => $entity,
            'result'      => $result,
      );


    }



    /**
     * Admin Post entities.
     *
     * @Route("/admin", name="post_admin")
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
    $query->add('select', 'p')
       ->add('from', 'ApplicationAnunciosBundle:Post p')
       ->add('orderBy', 'p.featured DESC, p.id DESC');

    // categoria?
    $category_id = $request->query->get('c');
    if ( $category_id ) {
       $query->add('where', 'p.category_id = :category_id')->setParameter('category_id', $category_id);
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
    $query = "SELECT COUNT(p.id) AS total, p.category_id FROM Post p GROUP BY p.category_id ORDER BY total DESC";
    $db = $this->get('database_connection');
        $categories = $db->fetchAll($query);



        return array('categories_aux' => $categories, 'pager' => $html, 'entities' => $entities);
    }

    /**
     * Feature Post entities.
     *
     * @Route("/admin/featured/{id}/{value}", name="post_admin_featured")
     * @Template()
     */
    public function featuredAction($id,$value)
    {

	    $session = $this->getRequest()->getSession();
	    if ( !$session->get('admin') ) {
	      return $this->redirect('/');
	    }
	
	    // existe post?
	    $em = $this->getDoctrine()->getEntityManager();
	    $entity = $em->getRepository('ApplicationAnunciosBundle:Post')->find($id);
	
	    if (!$entity) {
	        throw $this->createNotFoundException('Unable to find User entity.');
	    }
	
	    $entity->setFeatured($value);
	    $entity->setDateFeatured( new \DateTime("now") );
	    $em->persist($entity);
	    $em->flush();
	    
	    
	    if( isset( $_SERVER['HTTP_REFERER'] ) ){
	    	$url = $_SERVER['HTTP_REFERER'];
	    }else{
		    $url = $this->generateUrl('post_admin');
	    }
	
	    return $this->redirect( $url );
    }

    /**
     * Visible Post entities.
     *
     * @Route("/admin/visible/{id}/{value}", name="post_admin_visible")
     * @Template()
     */
    public function visibleAction($id,$value)
    {
    
	    // existe post?
	    $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('ApplicationAnunciosBundle:Post')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

	    $session = $this->getRequest()->getSession();
	    $user_id = $session->get('id');
	    
	    if ( $entity->getUserId() == $user_id || $session->get('admin') ) {
	    
	    
	    
	        $entity->setVisible($value);
	        $em->persist($entity);
	        $em->flush();
	        
	        
		    if( isset( $_SERVER['HTTP_REFERER'] ) ){
		    	$url = $_SERVER['HTTP_REFERER'];
		    }else{
		    
		    	if( $session->get('admin') ){
		    		$url = $this->generateUrl('post_admin');
		    	}else{
			    	$url = $this->generateUrl('post_dashboard');
			    }
		    }
	
	        return $this->redirect( $url );
	    
	      
	    }else{
		  return $this->redirect($this->generateUrl('user_welcome', array('back' => $_SERVER['REQUEST_URI'])));
	    }


    }

    /**
     * Admin Stats
     *
     * @Route("/stats", name="post_stats")
     * @Template()
     */
    public function statsAction()
    {

    $session = $this->getRequest()->getSession();
    $can_edit = ( $session->get('admin') OR $session->get('moderator') );
    if ( !$can_edit ) {
        return $this->redirect('/');
    }

    $em = $this->getDoctrine()->getEntityManager();






	/*    

    // usuarios registrados mes
    $query = $em->createQueryBuilder();
    $query->add('select', 'COUNT(u.id) AS total, u.date')
       ->add('from', 'ApplicationUserBundle:User u')
       ->andWhere("u.date BETWEEN '" . date('Y-m-d',strtotime("-1 month")) . "00:00:00' AND '" . date('Y-m-d') . " 23:59:59'")
       ->groupBy('u.date');
    $users_month_aux = $query->getQuery()->getResult();

    $users_month = array();
    if ( $users_month_aux ) {
      foreach ( $users_month_aux as $item ) {
        $k = (int)substr($item['date'],8,2);
        if ( !isset( $users_month[$k] ) ) $users_month[$k] = 1;
        else $users_month[$k] += $item['total'];
      }
    }

    // ofertas publicadas mes
    $query = $em->createQueryBuilder();
    $query->add('select', 'COUNT(p.id) AS total, p.date')
       ->add('from', 'ApplicationAnunciosBundle:Post p')
       ->andWhere("p.date BETWEEN '" . date('Y-m-d',strtotime("-1 month")) . "00:00:00' AND '" . date('Y-m-d') . " 23:59:59'")
       ->groupBy('p.date');
    $posts_month_aux = $query->getQuery()->getResult();

    $posts_month = array();
    if ( $posts_month_aux ) {
      foreach ( $posts_month_aux as $item ) {
        $k = (int)substr($item['date'],8,2);
        if ( !isset( $posts_month[$k] ) ) $posts_month[$k] = 1;
        else $posts_month[$k] += $item['total'];
      }
    }
    
    */
    
    

    $db = $this->get('database_connection');



	$year = date('Y');
	$month = date('m') - 1;
	if( $month == 0 ){
		$month = 12;
		$year--;
	}
	
	

	
    $interested_aux = $db->fetchAll("SELECT date, COUNT(id) as total FROM PostReply WHERE date < '" . $year . '-' . $month . '-31' . "' GROUP BY YEAR(date), MONTH(date) LIMIT 6");
    $jobs = $db->fetchAll("SELECT date, SUM(interested) as total FROM Post WHERE date < '" . $year . '-' . $month . '-31' . "' GROUP BY YEAR(date), MONTH(date) LIMIT 6");
    $users = $db->fetchAll("SELECT date, COUNT(id) as total FROM User WHERE date < '" . $year . '-' . $month . '-31' . "' GROUP BY YEAR(date), MONTH(date) LIMIT 6");
    
    /*
    if( count( $interested ) < 6 ){
    	for( $i = 0; $i < ( 6 - count( $interested ) ); $i++){
    		$blank[] = date('total' => 0, 'date' => false);
    	}
    	$interested = array_merge($blank,$interested_aux);
    }*/
    

    
    echo '<pre>';
    print_r($interested_aux);
    print_r($jobs);
    print_r($users);



    // usuarios registrados
    $query = "SELECT COUNT(u.id) AS total FROM User u";
    $result = $db->query($query)->fetch();
    $total_users = $result['total'];

    // usuarios referidos
    $query = "SELECT COUNT(u.id) AS total FROM User u WHERE u.ref_id IS NOT NULL";
    $result = $db->query($query)->fetch();
    $total_ref = $result['total'];

    // usuarios facebook
    $query = "SELECT COUNT(u.id) AS total FROM User u WHERE u.facebook_id IS NOT NULL";
    $result = $db->query($query)->fetch();
    $total_fb = $result['total'];

    // buscan empleo
    $query = "SELECT COUNT(u.id) AS total FROM User u WHERE u.unemployed = 1";
    $result = $db->query($query)->fetch();
    $total_unemployed = $result['total'];

    // freelance
    $query = "SELECT COUNT(u.id) AS total FROM User u WHERE u.freelance = 1";
    $result = $db->query($query)->fetch();
    $total_freelance = $result['total'];

    // recomendados
    $query = "SELECT COUNT(c.id) AS total FROM Comment c";
    $result = $db->query($query)->fetch();
    $total_comments = $result['total'];


    // anuncios
    $query = "SELECT COUNT(p.id) AS total FROM Post p";
    $result = $db->query($query)->fetch();
    $total_posts = $result['total'];

    // freelance
    $query = "SELECT COUNT(p.id) AS total FROM Post p WHERE p.type = 1";
    $result = $db->query($query)->fetch();
    $total_posts_freelance = $result['total'];

    // practicas
    $query = "SELECT COUNT(p.id) AS total FROM Post p WHERE p.type = 2";
    $result = $db->query($query)->fetch();
    $total_posts_internship = $result['total'];

    // eventos
    $query = "SELECT COUNT(e.id) AS total FROM Event e";
    $result = $db->query($query)->fetch();
    $total_events = $result['total'];

    // apuntados
    $query = "SELECT COUNT(e.id) AS total FROM EventUser e";
    $result = $db->query($query)->fetch();
    $total_joined = $result['total'];

    // places
    $query = "SELECT COUNT(p.id) AS total FROM Place p";
    $result = $db->query($query)->fetch();
    $total_places = $result['total'];


    // top posts
    $query = $em->createQueryBuilder();
    $query->add('select', 'p')
       ->add('from', 'ApplicationAnunciosBundle:Post p')
       ->add('orderBy', 'p.visits DESC')
       ->setMaxResults(10);
    $top_posts = $query->getQuery()->getResult();


    // top cities posts
    $query = "SELECT COUNT(p.id) AS total, c.name, c.id FROM Post p, City c WHERE p.city_id = c.id GROUP BY c.id ORDER BY total DESC LIMIT 10";
        $cities = $db->fetchAll($query);




        return array(
        	'total_places' => $total_places, 'total_events' => $total_events, 'total_joined' => $total_joined, 'cities' => $cities, 'top_posts' => $top_posts, 'total_users' => $total_users, 'total_ref' => $total_ref, 'total_fb' => $total_fb, 'total_unemployed' => $total_unemployed,
        	'total_freelance' => $total_freelance, 'total_comments' => $total_comments, 'total_posts' => $total_posts, 'total_posts_freelance' => $total_posts_freelance, 'total_posts_internship' => $total_posts_internship,
        	'interested' => $interested, 'jobs' => $jobs, 'users' => $users
        );
        
        //'users_month' => $users_month, 'posts_month' => $posts_month, 
    }

    /**
     * Posts widget
     *
     * @Route("/widget", name="post_widget")
     * @Template()
     */
    public function widgetAction()
    {
	    $request = $this->getRequest();
	    $em = $this->getDoctrine()->getEntityManager();
	

	
	    $query = $em->createQueryBuilder();
	    $query->add('select', 'p')
	       ->add('from', 'ApplicationAnunciosBundle:Post p')
	       ->add('orderBy', 'p.featured DESC, p.id DESC')
	       ->andWhere('p.visible = 1');
		       
	
	    $id = $request->query->get('id');
	    if( $id ){

		   $query->andWhere('p.user_id = :user_id')->setParameter('user_id', $id);
	       
	    }else{
		   
		   $query->setMaxResults(10);
		    
	    }
	    
	    
	    $entities = $query->getQuery()->getResult();




        return array('entities' => $entities );
    }
    
    
    /**
     * Posts dashboard
     *
     * @Route("/dashboard", name="post_dashboard")
     * @Template()
     */
    public function dashboardAction()
    {
    
    	// esta logueado?
        $session = $this->getRequest()->getSession();
        $user_id = $session->get('id');
        
        if( !$user_id ){
	         $url = $this->generateUrl('post');
	        return $this->redirect($url);
        }
    
        // obtener ofertas
	    $request = $this->getRequest();
	    $em = $this->getDoctrine()->getEntityManager();
	
	    $query = $em->createQueryBuilder();
	    $query->add('select', 'p')
	       ->add('from', 'ApplicationAnunciosBundle:Post p')
	       ->add('orderBy', 'p.visible DESC, p.id DESC')
	       ->andWhere('p.user_id = :user_id')->setParameter('user_id', $user_id);
		       
	    $entities = $query->getQuery()->getResult();
	    



        return array('entities' => $entities, 'user_id' => $user_id );
    }
    

    /**
     * Post replies
     *
     * @Route("/replies/{id}", name="post_replies")
     * @Template()
     */
    public function repliesAction($id)
    {
    
	    // existe post?
	    $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('ApplicationAnunciosBundle:Post')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

	    $session = $this->getRequest()->getSession();
	    $user_id = $session->get('id');
	    
	    if ( $entity->getUserId() == $user_id || $session->get('admin') ) {
	    
	    
		    $query = $em->createQueryBuilder();
		    $query->add('select', 'r')
		       ->add('from', 'ApplicationAnunciosBundle:PostReply r')
		       ->add('orderBy', 'r.id DESC')
		       ->andWhere('r.post_id = :id')->setParameter('id', $id);
		    $entities = $query->getQuery()->getResult();
	
	
	        return array('entity' => $entity, 'entities' => $entities);
	    

	      
	    }else{
		  return $this->redirect($this->generateUrl('user_welcome', array('back' => $_SERVER['REQUEST_URI'])));
	    }
	    
	   


    }

    
    /**
     * Post promote
     *
     * @Route("/promote", name="post_promote")
     * @Template()
     */
    public function promoteAction()
    {
    
    	// esta logueado?
        $session = $this->getRequest()->getSession();
        $user_id = $session->get('id');
        
        if( !$user_id ){
	        return $this->redirect($this->generateUrl('user_welcome', array('back' => $_SERVER['REQUEST_URI'])));
        }
        
	    $request = $this->getRequest();
	    $entity = false;
	    
	    // success?
	    $success = $request->query->get('success');
	    if( !$success ){
        
	        // existe oferta?
		    $id = $request->query->get('id');
		    if( $id ){
		        $em = $this->getDoctrine()->getEntityManager();
		        $entity = $em->getRepository('ApplicationAnunciosBundle:Post')->find($id);
		        if (!$entity) {
		            throw $this->createNotFoundException('Unable to find Post entity.');
		        }
	        }
        }

        return array('entity' => $entity, 'success' => $success );
    }

    /**
     * Get post slugs
     *
     * @Route("/slugs", name="post_slugs")
     */
    public function slugs()
    {
    $em = $this->getDoctrine()->getEntityManager();
    $qb = $em->createQueryBuilder()
       ->add('select', 'p')
       ->add('from', 'ApplicationAnunciosBundle:Post p')
       ->add('orderBy', 'p.id ASC');

    $entities = $qb->getQuery()->getResult();
    $total = count( $entities );

    for ( $i = 0; $i < $total; $i++ ) {

      $slug = $entities[$i]->getTitle();
      $city_id = $entities[$i]->getCityId();
      if ( $city_id ) {
        $city = $em->getRepository('ApplicationCityBundle:City')->find( $city_id );
        $slug .= ' ' . $city->getName();
      }
      $company = $entities[$i]->getCompany();
      if ( $company ) {
        $slug .= ' ' . $company;
      }


      $entities[$i]->setSlug(Util::slugify($slug));
      $em->persist($entities[$i]);
      $em->flush();

      
    }
    die();
  }

    /**
     * Finds and displays a Post entity.
     *
     * @Route("/{slug}-{id}/", requirements={"slug" = "[a-z0-9\-]+", "id" = "^\d+$"}, name="post_show")
     * @Template()
     */
    public function showAction($slug, $id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ApplicationAnunciosBundle:Post')->find($id);

        if (!$entity || !$entity->getVisible() ) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }

        $user = $em->getRepository('ApplicationUserBundle:User')->find($entity->getUserId());


        $session = $this->getRequest()->getSession();
        $contact = new \Application\AnunciosBundle\Entity\Contact;
        $id = $session->get('id');
        if ( $id ) {
          $user_login = $em->getRepository('ApplicationUserBundle:User')->find($id);
          $contact->setName( $user_login->getName() );
          $contact->setEmail( $user_login->getEmail() );
          $contact->setLinkedin( 'http://linkedin.com/in/' . $user_login->getLinkedinUrl() );
        }
        //$contact->setSubject( "RE: " . $entity->getTitle() );
        $contact_form = $this->createForm(new ContactType(), $contact);
        $contact_form_html = $contact_form->createView();



        $entities = false;
        $users = false;

        // ofertas relacionadas
        if ( $entity->getType() == 0 ) {
          $query = $em->createQueryBuilder();
          $query->add('select', 'p')
            ->add('from', 'ApplicationAnunciosBundle:Post p')
            ->add('where', 'p.category_id = :category_id')->setParameter('category_id', $entity->getCategoryId())
            ->andWhere('p.id != :id')->setParameter('id', $entity->getId())
            ->add('orderBy', 'p.id DESC')
            ->setMaxResults(5);
          $entities = $query->getQuery()->getResult();

        }

        // es diferente usuario, visitas + 1
        $session = $this->getRequest()->getSession();
        $session_id = $session->get('id');
        if ( $session_id != $entity->getUserId() ) {
          $entity->setVisits($entity->getVisits() + 1 );
          $em->persist($entity);
          $em->flush();
        }

        return array(
          'entity'       => $entity,
          'user'         => $user,
          'contact_form' => $contact_form_html,
          'entities'     => $entities
        );
    }




    function fixLocation( $post, $entity, $em ){        
      $location = $post->getLocation();
      if( $location ){
        $query = $em->createQuery("SELECT c1.id AS cit_id, c2.id AS cou_id, c1.name AS city, c2.name AS country FROM ApplicationCityBundle:City c1, ApplicationCityBundle:Country c2 WHERE c1.code = c2.code AND c1.name = :city ORDER BY c1.name ASC, c1.population DESC");
        $city = current( $query->setParameter('city', $location)->setMaxResults(1)->getResult() );
        if( $city ){
          $entity->setCityId( $city['cit_id'] );
          $entity->setCountryId( $city['cou_id'] );
          $entity->setLocation( $city['city'] . ', ' . $city['country'] );
        }
      }
  }

}
