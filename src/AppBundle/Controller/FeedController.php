<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Feed;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Feed controller.
 *
 * @Route("feeds")
 */
class FeedController extends Controller
{
    /**
     * Lists first page feed entities.
     *
     *
     * @Route("/", name="feed_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
//        $em = $this->getDoctrine()->getManager();
//
//        $feeds = $em->getRepository('AppBundle:Feed')->findAll();

        $em = $this->getDoctrine()->getManager();
        $dql   = "SELECT f FROM AppBundle:Feed f";
        $query = $em->createQuery($dql);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            1 /*page number*/,
            5 /*limit per page*/
        );

        return $this->render('feed/index.html.twig', array(
            'feeds' => $pagination,
        ));
    }

    /**
     * Lists all feed entities.
     *
     *
     * @Route("/page/{page}", name="feed_paginated", requirements={"page": "\d+"}), defaults={"page": 1}
     * @Method("GET")
     */
    public function indexPaginatedAction(Request $request, $page = 1)
    {
//        $em = $this->getDoctrine()->getManager();
//
//        $feeds = $em->getRepository('AppBundle:Feed')->findAll();

        $em = $this->getDoctrine()->getManager();
        $dql   = "SELECT f FROM AppBundle:Feed f";
        $query = $em->createQuery($dql);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $page /*page number*/,
            5 /*limit per page*/
        );

        return $this->render('feed/index.html.twig', array(
            'feeds' => $pagination,
        ));
    }

    /**
     * Creates a new feed entity.
     *
     * @Route("/new", name="feed_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $feed = new Feed();
        $form = $this->createForm('AppBundle\Form\FeedType', $feed);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($feed);
            $em->flush($feed);

            return $this->redirectToRoute('feed_show', array('id' => $feed->getId()));
        }

        return $this->render('feed/new.html.twig', array(
            'feed' => $feed,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a feed entity.
     *
     * @Route("/{id}", name="feed_show")
     * @Method("GET")
     */
    public function showAction(Feed $feed)
    {
        $deleteForm = $this->createDeleteForm($feed);

        return $this->render('feed/show.html.twig', array(
            'feed' => $feed,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing feed entity.
     *
     * @Route("/{id}/edit", name="feed_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Feed $feed)
    {
        $deleteForm = $this->createDeleteForm($feed);
        $editForm = $this->createForm('AppBundle\Form\FeedType', $feed);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('feed_edit', array('id' => $feed->getId()));
        }

        return $this->render('feed/edit.html.twig', array(
            'feed' => $feed,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a feed entity.
     *
     * @Route("/{id}", name="feed_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Feed $feed)
    {
        $form = $this->createDeleteForm($feed);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($feed);
            $em->flush($feed);
        }

        return $this->redirectToRoute('feed_index');
    }

    /**
     * Creates a form to delete a feed entity.
     *
     * @param Feed $feed The feed entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Feed $feed)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('feed_delete', array('id' => $feed->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
