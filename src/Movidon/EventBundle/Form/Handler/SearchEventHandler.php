<?php

namespace Movidon\EventBundle\Form\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityManager;
use Ideup\SimplePaginatorBundle\Paginator\Paginator;
use Movidon\BackendBundle\Util\SearchHelper;
use Doctrine\ORM\Query;

class SearchEventHandler
{

    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function handle(FormInterface $form, Request $request, Paginator $paginator)
    {
        $criteria=array();
        $form->bind($request);
        if ($form->isValid()) {
            $criteria = $form->getData();
        }

        $search = $this->em->getRepository("EventBundle:Event")->findBySearchCriteria($criteria);
        $events = $paginator->setItemsPerPage(SearchHelper::getLimitPerPage('medium'))->paginate($search)->getResult();

        return $events;
    }
}