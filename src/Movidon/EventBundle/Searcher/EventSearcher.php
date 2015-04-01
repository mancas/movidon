<?php
namespace Movidon\EventBundle\Searcher;

use Doctrine\ORM\EntityManager;
use Movidon\EventBundle\Util\Filter\EventFilter;
use Movidon\EventBundle\Util\SearchHelpers\SearchEvent;

class EventSearcher
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getSearchedValues($searchSpec, $year = null)
    {
        $searchEvent = new SearchEvent();

        if ($year) {
            $searchEvent->setYear($year);
        }

        if ($searchSpec) {
            $eventFilter = EventFilter::createFromSpec($searchSpec);
            if ($eventFilter->categorySlug && $eventFilter->categorySlug != "") {
                $searchEvent->setCategory($eventFilter->categorySlug);
            }
            
            if ($eventFilter->tagSlug && $eventFilter->tagSlug != "") {
                $searchEvent->setTag($eventFilter->tagSlug);
            }

            if ($eventFilter->city && $eventFilter->city != "") {
                $searchEvent->setCity($eventFilter->city);
            } else if ($eventFilter->province && $eventFilter->province != "") {
                $searchEvent->setProvince($eventFilter->province);
            }

            if ($eventFilter->date && $eventFilter->date != "") {
                $searchEvent->setDate($eventFilter->date);
                if ($year) {
                    $this->modifyDate($searchEvent, $year);
                }
            }

            if ($eventFilter->page) {
                $page = $eventFilter->page;
            }
        } else {
            $cities = array();
            $events = array();
            $page = 1;
            return array($cities, $searchEvent, $events, $page);
        }

        return $this->getObjectsFromItemFilter($searchEvent, $page);
    }

    private function getObjectsFromItemFilter($searchEvent, $page)
    {
        if ($searchEvent->getProvince()) {
            $province = $this->em->getRepository('LocationBundle:Province')->findOneBySlug($searchEvent->getProvince());
            $cities = $this->em->getRepository('LocationBundle:City')->findBy(array('province' => $province->getId()));
        } else if ($searchEvent->getCity()) {
            $city = $this->em->getRepository('LocationBundle:City')->findOneBySlug($searchEvent->getCity());
            $cities = $this->em->getRepository('LocationBundle:City')->findBy(array('province' => $city->getProvince()->getId()));
            $searchEvent->setProvince($city->getProvince()->getSlug());
        } else {
            $cities = array();
        }

        if ($searchEvent->getDate()) {
            $events = $this->em->getRepository('EventBundle:Event')->findEventDQL($searchEvent);
        } else {
            $events = array();
        }

        return array($cities, $searchEvent, $events, $page);
    }

    private function modifyDate($searchEvent, $year)
    {
        $date = $searchEvent->getDate();
        $date = new \DateTime($date);
        $newDate = $date->format('d') . '-' . $date->format('m') . '-' . $year;

        $searchEvent->setDate($newDate);
    }
}