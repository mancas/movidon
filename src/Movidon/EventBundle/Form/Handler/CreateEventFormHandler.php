<?php

namespace Movidon\EventBundle\Form\Handler;

use Movidon\BackendBundle\Entity\AdminUser;
use Movidon\EventBundle\Entity\EventDate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityManager;

class CreateEventFormHandler
{
    private $eventManager;

    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    public function handle(FormInterface $form, Request $request)
    {
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $event = $form->getData();
                $city = $request->request->get('l');
                $this->eventManager->save($event, $city);

                return true;
            }
        }

        return false;
    }
}