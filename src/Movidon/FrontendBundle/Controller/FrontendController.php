<?php

namespace Movidon\FrontendBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class FrontendController extends CustomController
{
    const ITEMS_LIMIT_DQL = 12;

    public function indexAction()
    {
        $em = $this->getEntityManager();
        $nextEvents = $em->getRepository('EventBundle:Event')->findNextEventsFromDate(new \DateTime('now'), 1);
        $firstEvent = null;
        if (count($nextEvents) > 0) {
            $firstEvent = $nextEvents[0];
        }
        return $this->render('FrontendBundle:Pages:home.html.twig', array('event' => $firstEvent));
    }
}
