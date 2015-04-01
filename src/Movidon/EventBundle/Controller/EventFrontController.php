<?php

namespace Movidon\EventBundle\Controller;

use Movidon\EventBundle\Entity\Event;
use Movidon\EventBundle\Form\Type\EventType;
use Movidon\FrontendBundle\Controller\CustomController;
use Movidon\FrontendBundle\Util\ArrayHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Movidon\FrontendBundle\Util\Paginator;
use Movidon\BlogBundle\Util\ArchiveDate;
use Movidon\EventBundle\Util\SearchHelpers\SearchEvent;

class EventFrontController extends CustomController
{
    const DQL_LIMIT = 5;
    const EVENTS_PER_PAGE = 15;

    public function indexAction(Request $request, $searchSpec = null, $year = null)
    {
        $em = $this->getEntityManager();

        if ($year) {
            $date = new \DateTime($year . '-01-01');
        } else {
            $date = new \DateTime('now');
        }

        list($cities, $searchEvent, $allEvents, $page) = $this->container->get('event.searcher')->getSearchedValues($searchSpec, $year);
        $calendarAction = false;
        if ($searchEvent->getYear() != null && empty($searchSpec)) {
            $calendarAction = true;
        }

        $showPopover = true;
        if ($searchEvent->getYear() != null || !empty($searchSpec)) {
            $showPopover = false;
        }

        if (count($allEvents) == 0) {
            $searchEvent->setYear($date->format('Y'));
            $allEvents = $em->getRepository('EventBundle:Event')->findEventDQL($searchEvent);
        }

        $events = $this->prepareEvents($searchEvent, $date->format('Y'));

        $tags = $em->getRepository('EventBundle:Tag')->findAll();
        $categories = $em->getRepository('CategoryBundle:EventCategory')->findAll();
        $provinces = $em->getRepository('LocationBundle:Province')->findAll();
        $paginator = new Paginator(array('page' => $page, 'total' => count($allEvents), 'limit' => self::EVENTS_PER_PAGE));
        $index = count($events) > 3 && $page < 4;
        list($category, $province, $city, $year, $day) = array($searchEvent->getCategory(), $searchEvent->getProvince(), $searchEvent->getCity(), $searchEvent->getYear(), $searchEvent->getDate());

        $description="";
        foreach ($categories as $cat){
            if ($cat->getSlug()==$category){
                $description = $cat->getDescription();
            }
        }

        return $this->render('EventBundle:Event:calendar-event.html.twig', array('events' => $events,
                                                                                 'title' => $this->getTitle($category, $province, $city, $year, $day),
                                                                                 'description'=>$description,
                                                                                 'day' => $day,
                                                                                 'sel_tag' => $searchEvent->getTag(),
                                                                                 'tags' => $tags,
                                                                                 'year' => $date->format('Y'),
                                                                                 'provinces' => $provinces,
                                                                                 'sel_prov' => $searchEvent->getProvince(),
                                                                                 'sel_city' => $searchEvent->getCity(),
                                                                                 'cities' => $cities,
                                                                                 'searchSpec' => $searchSpec,
                                                                                 'paginator' => $paginator,
                                                                                 'page' => $page,
                                                                                 'index' => $index,
                                                                                 'allEvents' => $allEvents,
                                                                                 'calendarAction' => $calendarAction,
                                                                                 'showPopover' => $showPopover,
                                                                                 'sel_cat' => $searchEvent->getCategory(),
                                                                                 'categories' => $categories ));
    }

    private function prepareEvents(SearchEvent $searchEvent, $year)
    {
        $em = $this->getEntityManager();
        $eventsOneDay = $em->getRepository('EventBundle:Event')->findEventsFromYearOrderedByMonth($searchEvent, $year);
        $eventsMoreDays = $em->getRepository('EventBundle:Event')->findEventsDateOrderByMonth($searchEvent, $year);
        $eventsOneDay = ArrayHelper::multiLevelArrayEventsToSingleArrayEvents($eventsOneDay);
        return $eventsOneDay;
/*
        foreach ($eventsMoreDays as $event) {
            if (isset($eventsOneDay[$event['date']])) {
                $events[$event['date']] = $event['events'] + $eventsOneDay[$event['date']];
            } else {
                $events[$event['date']] = $event['events'];
            }
        }
        return $events;
*/
    }

    private function getTitle($category, $province, $city, $year, $date = null, $page = null)
    {
        list ($category, $city, $province) = $this->getItemSearchObjects($category, $city, $province);
        $title = "Eventos de pop-up stores, corners y showrooms en España";
        if ($category) {
            $title = "Eventos de " . lcfirst($category->getName());
            if ($city) {
                $title .= " en {$city->getName()}";
            } else if ($province) {
                $title .= " en provincia de {$province->getName()}";
            }
        } else {
            if ($city) {
                $title = "Eventos en {$city->getName()}";
            } else if ($province) {
                $title = "Eventos en provincia de {$province->getName()}";
            }
        }
        if (!$date && $year) {
            $title .= " en " . $year;
        }

        if ($page && $page > 1 && $title) {
            $title .= " | $page ";
        }

        return $title;
    }

    private function getItemSearchObjects($cat, $cit = null, $pro = null)
    {
        $em = $this->getEntityManager();
        list($category, $city, $province) = array($cat, $cit, $pro);

        if (is_string($category)) {
            $category = $em->getRepository('CategoryBundle:EventCategory')->findOneBySlug($category);
        }
        if (is_string($city)) {
            $city = $em->getRepository('LocationBundle:City')->findOneBySlug($city);
        }
        if (is_string($province)) {
            $province = $em->getRepository('LocationBundle:Province')->findOneBySlug($province);
        }

        return array($category, $city, $province);
    }

    /**
     * @param Event $event
     *
     * @Template("FrontendBundle:Commons:pop-box.html.twig")
     *
     * @return array
     */
    public function eventBoxAction(Event $event, $year, $day = null)
    {
        return array('obj' => $event, 'type'=>'event', 'year' => $year, 'day' => $day);
    }

    /**
     * @ParamConverter("$event", class="EventBundle:Event")
     */
    public function eventAction(Event $event)
    {
        //TODO related events
        $em = $this->getEntityManager();
        $lastEvents = $em->getRepository('EventBundle:Event')->findEventDQL(new SearchEvent(), 5);
        $tags = $em->getRepository('EventBundle:Tag')->findAll();
        $categories = $em->getRepository('CategoryBundle:EventCategory')->findAll();
        $relatedEvents = $em->getRepository('EventBundle:Event')->findRelatedEventsFromTags($event, self::DQL_LIMIT);
        $nextEvents = $em->getRepository('EventBundle:Event')->findNextEventsFromDate(new \DateTime('now'), self::DQL_LIMIT, 'groupByItem', $event);
        for ($i = 0; $i < 5; $i++) {
            $date = new \DateTime('today');
            $interval = new \DateInterval('P'.$i.'M');
            $date->sub($interval);
            $dates[] = new ArchiveDate($date);
        }
        list($relatedEventsByCategory, $relatedEventsByCity, $relatedEventsByProvince) = $this->getSimilarEvents($event);

        return $this->render('EventBundle:Event:event.html.twig', array('event' => $event,
                                                                        'lastsEvents' => $lastEvents, 
                                                                        'tags' => $tags, 
                                                                        'categories' => $categories, 
                                                                        'dates' => $dates,
                                                                        'dateFrom' => false, 
                                                                        'dateTo' => false,
                                                                        'sel_tag'=> false,
                                                                        'relatedEvents' => $relatedEvents,
                                                                        'fromSingleEvent' => true,
                                                                        'eventsByCategory' => $relatedEventsByCategory,
                                                                        'eventsByProvince' => $relatedEventsByProvince,
                                                                        'eventsByCity' => $relatedEventsByCity,
                                                                        'nextEvents' => $nextEvents));
    }

    private function getSimilarEvents($event)
    {
        $em = $this->getEntityManager();
        $searchBy = new SearchEvent();
        $searchBy->setCategory($event->getCategory()->getSlug());
        $relatedEventsByCategory = $em->getRepository('EventBundle:Event')->findEventDQLSEO($searchBy, Event::RELATED_ITEMS_LIMIT);
        if ($event->getCity()) {
            $searchBy->setCategory(null);
            $searchBy->setCity($event->getCity()->getSlug());
            $relatedEventsByCity = $em->getRepository('EventBundle:Event')->findEventDQLSEO($searchBy, Event::RELATED_ITEMS_LIMIT);

            $searchBy->setCity(null);
            $searchBy->setProvince($event->getCity()->getProvince()->getSlug());
            $relatedEventsByProvince = $em->getRepository('EventBundle:Event')->findEventDQLSEO($searchBy, Event::RELATED_ITEMS_LIMIT);
        }

        return array($relatedEventsByCategory, $relatedEventsByCity, $relatedEventsByProvince);
    }

    private function getSimilarSearchedEvents($searchBy)
    {
        $em = $this->getEntityManager();
        $relatedItemsByCategory = $em->getRepository('ItemBundle:Item')->findItemDQLSEO($searchBy, Item::RELATED_ITEMS_LIMIT);
        $relatedItemsByCity = $em->getRepository('ItemBundle:Item')->findItemDQLSEO($searchBy, Item::RELATED_ITEMS_LIMIT);
        $relatedItemsByProvince = $em->getRepository('ItemBundle:Item')->findItemDQLSEO($searchBy, Item::RELATED_ITEMS_LIMIT);

        return array($relatedItemsByCategory, $relatedItemsByCity, $relatedItemsByProvince);
    }

    public function getEventsAction()
    {
        $jsonResponse = json_encode(array('ok' => false));
        $request = $this->getRequest();
        $month = $request->request->get('month');
        $year = $request->request->get('year');
        if ($request->isXmlHttpRequest()) {
            $em = $this->getEntityManager();
            $dates = $em->getRepository('EventBundle:Event')->findEventDatesFromDate($month, $year);
            $response = array();
            foreach($dates as $date) {
                $eventArray = array();
                $events = $em->getRepository('EventBundle:Event')->findEventsFromEventDate($date['date']);
                foreach ($events as $event) {
                    if ($event->getImage()) {
                        $path = $event->getImage()->getImageEventCalendarThumbnail()->getWebFilePath();
                        $path = $this->container->get('templating.helper.assets')->getUrl($path);

                        $eventItem = array('id' => $event->getId(), 'title' => $event->getTitle(),
                            'img' => $path);
                    }
                    $eventArray[] = $eventItem;
                }
                $response[$date['date']->format('Y-m-d')] = $eventArray;
            }
            if (!$response) {
                return $this->noEvents();
            }

            $jsonResponse = json_encode(array('ok' => true, 'dates' => $response));
        }

        return $this->getHttpJsonResponse($jsonResponse);
    }

    public function getEventDetailAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $eventDate = $request->query->get('date');
        $event = $em->getRepository('EventBundle:Event')->findOneById($request->query->get('id'));

        return $this->render('EventBundle:Event:event-detail.html.twig', array('event' => $event, 'dateEvent' => $eventDate));
    }

    public function getNextEventDetailAction(Request $request)
    {
        $date = $request->query->get('date');
        $em = $this->get('doctrine')->getManager();
        $event = $em->getRepository('EventBundle:Event')->findNextEventsFromDate($date, 1);
        $event = ArrayHelper::multiLevelArrayToSingleLevel($event);
        $events = $em->getRepository('EventBundle:Event')->findEventsFromEventDate($event['date']);
        $response['event'] = $events[array_rand($events)];
        $response['date'] = $event['date'];
        if (count($event) == 0) {
            $eventsFinders = ArrayHelper::multiLevelArrayToSingleLevel($em->getRepository('EventBundle:Event')->findEventsFromEventDate($date));
            $response['event'] = $eventsFinders[array_rand($eventsFinders)];
            $response['date'] = $date;
        }

        return $this->render('EventBundle:Event:event-detail.html.twig', array('event' => $response['event'], 'dateEvent' => $response['date']));
    }

    public function getPrevEventDetailAction(Request $request)
    {
        $date = $request->query->get('date');
        $em = $this->get('doctrine')->getManager();
        $event = $em->getRepository('EventBundle:Event')->findPrevEventsFromDate($date, 1);
        $event = ArrayHelper::multiLevelArrayToSingleLevel($event);
        $events = $em->getRepository('EventBundle:Event')->findEventsFromEventDate($event['date']);
        $response['event'] = $events[array_rand($events)];
        $response['date'] = $event['date'];
        if (count($event) == 0) {
            $eventsFinders = ArrayHelper::multiLevelArrayToSingleLevel($em->getRepository('EventBundle:Event')->findEventsFromEventDate($date));
            $response['event'] = $eventsFinders[0];
            $response['date'] = $date;
        }

        return $this->render('EventBundle:Event:event-detail.html.twig', array('event' => $response['event'], 'dateEvent' => $response['date']));
    }

    public function getEventsTemplateAction()
    {
        $request = $this->getRequest();
        $date = $request->query->get('date');
        $date = new \DateTime($date);
        $search = $request->query->get('search');
        $em = $this->getEntityManager();
        $events = $em->getRepository('EventBundle:Event')->findEventByDate($date, $search);

        return $this->render('EventBundle:Event:events.html.twig', array('events' => $events, 'year' => $date->format('Y')));
    }

    private function noEvents()
    {
        $jsonResponse = json_encode(array('ok' => false, 'message' => 'No hay eventos'));

        return $this->getHttpJsonResponse($jsonResponse);
    }
}
