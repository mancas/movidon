<?php

namespace Movidon\BackendBundle\Controller;

use Movidon\EventBundle\Entity\Event;
use Movidon\EventBundle\Form\Type\EventType;
use Movidon\FrontendBundle\Controller\CustomController;
use Movidon\ImageBundle\Form\Type\ImageType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Movidon\ImageBundle\Form\Type\ImageNotRequiredType;
use Movidon\BackendBundle\Util\SearchHelper;
use Movidon\EventBundle\Form\Type\SearchEventType;

class EventController extends CustomController
{
    public function indexAction(Request $request)
    {
        $paginator = $this->get('ideup.simple_paginator');
        $form = $this->createForm(new SearchEventType());
        $events = $this->get('event.search_event_form_handler')->handle($form, $request, $paginator);

        return $this->render('BackendBundle:Event:index.html.twig', array('events' => $events, 'form'=>$form->createView(), 'paginator'=>$paginator));
    }

    public function createAction(Request $request)
    {
        $event = new Event();
        $form = $this->createForm(new EventType(), $event);
        $formImage = $this->createForm(new ImageType());
        $provinces = $this->getEntityManager()->getRepository('LocationBundle:Province')->findAll();
        $formHandler = $this->get('event.create_event_form_handler');
        //$dates['eventDates'] = $request->get('event_dates');
        //$dates['periods'] = $request->get('periodos');
        if ($formHandler->handle($form, $request)) {
            $formImgHandler = $this->get('image.create_image_form_handler');
            if ($formImgHandler->handleToEvent($formImage, $request, $event)) {
                $this->setTranslatedFlashMessage("Evento creado");
            } else {
                $this->setTranslatedFlashMessage("Error en la subida de imágenes");
            }

            return $this->redirect($this->generateUrl('admin_event_index'));
        }

        return $this->render('BackendBundle:Event:create.html.twig', array('form' => $form->createView(),
            'formImage' => $formImage->createView(),
            'provinces' => $provinces));
    }

    /**
     * @ParamConverter("$event", class="EventBundle:Event")
     */
    public function editAction(Event $event)
    {
        $form = $this->createForm(new EventType(), $event);
        $formHandler = $this->get('event.create_event_form_handler');
        $formImage = $this->createForm(new ImageNotRequiredType());
        $request = $this->getRequest();
        $provinces = $this->getEntityManager()->getRepository('LocationBundle:Province')->findAll();
        $cities = array();
        $province = null;
        if ($event->getCity()) {
            $province = $event->getCity()->getProvince();
            $cities = $this->getEntityManager()->getRepository('LocationBundle:City')->findBy(array('province' => $province));
        }
        $dates['eventDates'] = $request->get('event_dates');
        $dates['periods'] = $request->get('periodos');
        if ($formHandler->handle($form, $request, $this->get('security.context')->getToken()->getUser(), $dates)) {
            $formImgHandler = $this->get('image.create_image_form_handler');
            if (array_key_exists('file', $request->files->get('image'))) {
                if ($formImgHandler->handleToEvent($formImage, $request, $event)) {
                    $this->setTranslatedFlashMessage("Evento editado");
                } else {
                    $this->setTranslatedFlashMessage("Error en la subida de imágenes");
                }
            }

            return $this->redirect($this->generateUrl('admin_event_index'));
        }

        return $this->render('EventBundle:Event:backend/create.html.twig',
            array('form' => $form->createView(), 'formImage' => $formImage->createView(),
                'event' => $event,
                'edition' => true,
                'provinces' => $provinces,
                'provinceEvent' => $province,
                'cities' => $cities));
    }

    /**
     * @ParamConverter("$event", class="EventBundle:Event")
     */
    public function deleteAction(Event $event)
    {
        $em = $this->getEntityManager();
        $em->remove($event);
        $em->flush();
        $this->setTranslatedFlashMessage("Se ha eliminado el evento");

        return $this->redirect($this->generateUrl('admin_event_index'));
    }

    /**
     * @ParamConverter("$event", class="EventBundle:Event")
     */
    public function publishAction(Event $event)
    {
        $em = $this->getEntityManager();
        $date = new \DateTime('now');
        $event->setPublished($date);
        $em->persist($event);
        $em->flush();
        $this->setTranslatedFlashMessage("Evento publicado");

        return $this->redirect($this->generateUrl('admin_event_index'));
    }

}
