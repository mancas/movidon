<?php

namespace Movidon\EventsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('EventsBundle:Default:index.html.twig', array('name' => $name));
    }
}
