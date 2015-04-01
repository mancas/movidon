<?php

namespace Movidon\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity()
 */
class Tag extends \Movidon\FrontendBundle\Entity\Tag
{
    /**
     * @ORM\ManyToMany(targetEntity="Movidon\EventBundle\Entity\Event", mappedBy="tags")
     */
    protected $events;

    protected function initializeObjects()
    {
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param Event $event
     */
    public function addEvent(Event $event)
    {
        $event->addTag($this);
        $this->events->add($event);
    }

    /**
     * @param Event $event
     */
    public function removeEvent(Event $event)
    {
        $this->events->removeElement($event);
    }

    /**
     * @return @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getEvents()
    {
        return $this->events;
    }

} 