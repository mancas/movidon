<?php

namespace Movidon\EventBundle\Controller;

use Movidon\FrontendBundle\Controller\CustomController;
use Movidon\EventBundle\Entity\Event;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class EventLinksController extends CustomController
{
    /**
     * @Template("EventBundle:Event:commons/links.html.twig")
     *
     * @return array
     */
    public function bestCategoriesEventsLinksAction($year)
    {
        $em = $this->getEntityManager();
        $categories = $em->getRepository('EventBundle:Event')->getCategoriesWithMoreEvents($year, Event::RELATED_ITEMS_LIMIT);

        return array('categories' => $categories, 'year' => $year);
    }

    /**
     * @Template("EventBundle:Event:commons/links.html.twig")
     *
     * @return array
     */
    public function bestCitiesEventsLinksAction($year)
    {
        $em = $this->getEntityManager();
        $cities = $em->getRepository('EventBundle:Event')->getCitiesWithMoreEvents($year, Event::RELATED_ITEMS_LIMIT);

        return array('cities' => $cities, 'year' => $year);
    }

    /**
     * @Template("EventBundle:Event:commons/links.html.twig")
     *
     * @return array
     */
    public function bestProvincesEventsLinksAction($year)
    {
        $em = $this->getEntityManager();
        $provinces = $em->getRepository('EventBundle:Event')->getProvincesWithMoreEvents($year, Event::RELATED_ITEMS_LIMIT);

        return array('provinces' => $provinces, 'year' => $year);
    }

    /**
     * @Template("EventBundle:Event:commons/links.html.twig")
     *
     * @return array
     */
    public function provincesWithCategoryEventsLinksAction($categorySlug, $year)
    {
        $em = $this->getEntityManager();
        $category = $em->getRepository('CategoryBundle:EventCategory')->findOneBy(array('slug' => $categorySlug));
        $items = $em->getRepository('EventBundle:Event')->getEventsWithProvinceFromCategory($categorySlug, $year, Event::RELATED_ITEMS_LIMIT);

        return array('eventsProvinceCategory' => $items, 'category' => $category, 'year' => $year);
    }

    /**
     * @Template("EventBundle:Event:commons/links.html.twig")
     *
     * @return array
     */
    public function citiesWithCategoryEventsLinksAction($categorySlug, $year)
    {
        $em = $this->getEntityManager();
        $category = $em->getRepository('CategoryBundle:EventCategory')->findOneBy(array('slug' => $categorySlug));
        $items = $em->getRepository('EventBundle:Event')->getEventsWithCityFromCategory($categorySlug, $year, Event::RELATED_ITEMS_LIMIT);

        return array('eventsCityCategory' => $items, 'category' => $category, 'year' => $year);
    }

    /**
     * @Template("EventBundle:Event:commons/links.html.twig")
     *
     * @return array
     */
    public function citiesWithProvinceAndCategoryEventsLinksAction($provinceSlug, $categorySlug, $year, $citySlug = null)
    {
        $em = $this->getEntityManager();
        $category = $em->getRepository('CategoryBundle:EventCategory')->findOneBy(array('slug' => $categorySlug));
        $province = $em->getRepository('LocationBundle:Province')->findOneBy(array('slug' => $provinceSlug));
        $items = $em->getRepository('EventBundle:Event')->getEventsWithCityFromCategoryAndProvince($provinceSlug, $categorySlug, $citySlug, $year, Event::RELATED_ITEMS_LIMIT);

        return array('eventsCategoryProvinces' => $items, 'category' => $category, 'province' => $province, 'year' => $year);
    }

    /**
     * @Template("EventBundle:Event:commons/links.html.twig")
     *
     * @return array
     */
    public function categoriesWithProvinceEventsLinksAction($provinceSlug, $citySlug = null, $year)
    {
        $em = $this->getEntityManager();
        $city = $em->getRepository('LocationBundle:City')->findOneBy(array('slug' => $citySlug));
        $province = $em->getRepository('LocationBundle:Province')->findOneBy(array('slug' => $provinceSlug));
        $items = $em->getRepository('EventBundle:Event')->getEventsWithCategoryFromProvince($provinceSlug, $citySlug, $year, Event::RELATED_ITEMS_LIMIT);

        return array('eventsCategoryFromProvinces' => $items, 'province' => $province, 'city' => $city, 'year' => $year);
    }

    /**
     * @Template("EventBundle:Event:commons/links.html.twig")
     *
     * @return array
     */
    public function citiesWithProvinceEventsLinksAction($provinceSlug, $citySlug = null, $year)
    {
        $em = $this->getEntityManager();
        $province = $em->getRepository('LocationBundle:Province')->findOneBy(array('slug' => $provinceSlug));
        $items = $em->getRepository('EventBundle:Event')->getEventsWithCityFromProvince($provinceSlug, $citySlug, $year, Event::RELATED_ITEMS_LIMIT);

        return array('eventsCityFromProvinces' => $items, 'province' => $province, 'year' => $year);
    }
}
