<?php

namespace Movidon\EventBundle\Form\Handler;

use Movidon\BackendBundle\Entity\AdminUser;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Movidon\EventBundle\Entity\Event;

class EventManager
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function save(Event $event, $city)
    {
        if ($city) {
            $eventCity = $this->entityManager->getRepository('LocationBundle:City')->findOneBySlug($city);
            $event->setCity($eventCity);
        }
        $this->entityManager->persist($event);
        $this->entityManager->flush();
    }

}
