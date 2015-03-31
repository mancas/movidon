<?php

namespace Movidon\FrontendBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class FrontendController extends CustomController
{
    const ITEMS_LIMIT_DQL = 12;

    public function indexAction()
    {
        return $this->render('FrontendBundle:Pages:home.html.twig');
    }
}
