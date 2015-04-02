<?php

namespace Movidon\FrontendBundle\Form\Handler;

use Movidon\FrontendBundle\Entity\Tag;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

class TagManager
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function save(Tag $tag)
    {
        $this->entityManager->persist($tag);
        $this->entityManager->flush();
    }
}
