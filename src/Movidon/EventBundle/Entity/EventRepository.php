<?php

namespace Movidon\EventBundle\Entity;

use Movidon\FrontendBundle\Entity\CustomEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\Query;
use Movidon\EventBundle\Util\SearchHelpers\SearchEvent;

class EventRepository extends CustomEntityRepository
{
    protected $specialFields = array('published', 'tag', 'notPublished');

    protected function addToQueryBuilderSpecialFieldPublished(\Doctrine\ORM\QueryBuilder &$qb, $value)
    {
        $qb->andWhere($qb->expr()->isNotNull('i.published', 0));
    }
    
    protected function addToQueryBuilderSpecialFieldNotPublished(\Doctrine\ORM\QueryBuilder &$qb, $value)
    {
        $qb->andWhere($qb->expr()->isNull('i.published', 0));
    }
    
    protected function addToQueryBuilderSpecialFieldTag(\Doctrine\ORM\QueryBuilder &$qb, $value)
    {
        $qb->join('i.tags', 't', 'WITH', 't.id = :tag')->setParameter('tag', $value);
    }

    public function findEventDQL(SearchEvent $data, $limit = null)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e','i');
    
        $qb->leftJoin('e.image', 'i');
        $qb->leftJoin('e.eventDates', 'eventDate');
        $qb->addOrderBy('e.id','DESC');

        $and = $qb->expr()->andx();
        $or = $qb->expr()->orx();
    
        if ($data->getCategory()!=null) {
            $qb->leftJoin('e.category', 'cat');
            $and->add($qb->expr()->eq('cat.slug', '\''. $data->getCategory() .'\''));
        }
        
        if ($data->getTag()!=null) {
            $qb->leftJoin('e.tags', 'tags');
            $and->add($qb->expr()->in('tags.slug', '\''. $data->getTag() .'\''));
        }
        if ($data->getProvince() != null) {
            $qb->leftJoin('e.city', 'city');
            $qb->leftJoin('city.province', 'province');
            $and->add($qb->expr()->in('province.slug', '\''. $data->getProvince() .'\''));
            if ($data->getCity() != null) {
                $and->add($qb->expr()->in('city.slug', '\''. $data->getCity() .'\''));
            }
        }

        if ($data->getDate() != null) {
            $date = $data->getDate();
            if (is_string($date)) {
                $date = new \DateTime($date);
            }
            $and->add($qb->expr()->eq('eventDate.date', '\''.$date->format('Y-m-d H:i:s').'\''));
        } else {
            if ($data->getYear() != null) {
                $dateFrom = '01-01-' . $data->getYear();
                $dateTo = '01-01-' . ($data->getYear() + 1);
                $dateFrom = new \DateTime($dateFrom);
                $dateTo = new \DateTime($dateTo);

                $and->add($qb->expr()->gte('eventDate.date', '\''.$dateFrom->format('Y-m-d H:i:s').'\''));
                $and->add($qb->expr()->lt('eventDate.date', '\''.$dateTo->format('Y-m-d H:i:s').'\''));
            }
        }

        $and->add($qb->expr()->isNotNull('e.published'));
        $qb->where($and);
    
        if (isset($limit)) {
            $qb->setMaxResults($limit);
        }
        return $qb->getQuery()->getResult();
    }

    public function findNextEventsFromDate($date = null, $limit = null, $groupBy = null, $event = null)
    {
        $qb = $this->createQueryBuilder('e');

        if (!$date)
            $date = new \DateTime('now');
        else {
            if (is_string($date)) {
                $date = new \DateTime($date);
            }
        }

        $qb->leftJoin('e.eventDates', 'eventDateSub');
        $qb->addOrderBy('eventDateSub.date', 'ASC');
        if ($groupBy) {
            $qb->addGroupBy('e');
        }
        $qb->select('e as event', 'eventDateSub.date');
        $and = $qb->expr()->andx();
        $and->add($qb->expr()->isNotNull('e.published'));
        $qb->where($qb->expr()->gt('eventDateSub.date', '\''.$date->format('Y-m-d H:i:s').'\''));
        if ($event) {
            $and->add($qb->expr()->neq('e.id',$event->getId()));
        }
        $qb->andwhere($and);

        if (isset($limit))
            $qb->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function findPrevEventsFromDate($date = null, $limit = null)
    {
        $qb = $this->createQueryBuilder('e');

        if (!$date)
            $date = new \DateTime('now');
        else {
            if (is_string($date)) {
                $date = new \DateTime($date);
            }
        }

        $qb->leftJoin('e.eventDates', 'eventDateSub');
        $qb->addOrderBy('eventDateSub.date', 'DESC');
        $qb->select('e as event', 'eventDateSub.date');
        $and = $qb->expr()->andx();
        $and->add($qb->expr()->isNotNull('e.published'));
        $qb->where($qb->expr()->lt('eventDateSub.date', '\''.$date->format('Y-m-d H:i:s').'\''));
        $qb->andwhere($and);

        if (isset($limit))
            $qb->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function findEventsFromDate($month = null, $year = null)
    {
        $fullDate = $year . '-' . $month . '-01';
        $dateFrom = new \DateTime($fullDate);
        $dateTo = new \DateTime($fullDate);
        $dateTo->modify('+1 month');

        $qb = $this->createQueryBuilder('e');

        $qb->select('e');
        $qb->leftJoin('e.eventDates', 'eventDate');

        $and = $qb->expr()->andx();
        $and->add($qb->expr()->isNotNull('e.published'));
        if ($month && $year) {
            $and->add($qb->expr()->gte('eventDate.date', '\''.$dateFrom->format('Y-m-d H:i:s').'\''));
            $and->add($qb->expr()->lt('eventDate.date', '\''.$dateTo->format('Y-m-d H:i:s').'\''));
        }

        $qb->where($and);

        return $qb->getQuery()->getResult();
    }

    public function findEventsFromEventDate($eventDate)
    {
        if (!$eventDate)
            $eventDate = new \DateTime('now');
        else {
            if (is_string($eventDate)) {
                $eventDate = new \DateTime($eventDate);
            }
        }
        $qb = $this->createQueryBuilder('e');

        $qb->select('e');
        $qb->leftJoin('e.eventDates', 'eventDate');

        $and = $qb->expr()->andx();
        $and->add($qb->expr()->isNotNull('e.published'));
        $and->add($qb->expr()->eq('eventDate.date', '\''.$eventDate->format('Y-m-d H:i:s').'\''));

        $qb->where($and);

        return $qb->getQuery()->getResult();
    }

    public function findEventDatesFromDate($month = null, $year = null)
    {
        $fullDate = $year . '-' . $month . '-01';
        $dateFrom = new \DateTime($fullDate);
        $dateTo = new \DateTime($fullDate);
        $dateTo->modify('+1 month');

        $qb = $this->createQueryBuilder('e');

        $qb->select('eventDate.date');
        $qb->leftJoin('e.eventDates', 'eventDate');

        $and = $qb->expr()->andx();
        $and->add($qb->expr()->isNotNull('e.published'));
        if ($month && $year) {
            $and->add($qb->expr()->gte('eventDate.date', '\''.$dateFrom->format('Y-m-d H:i:s').'\''));
            $and->add($qb->expr()->lt('eventDate.date', '\''.$dateTo->format('Y-m-d H:i:s').'\''));
        }

        $qb->where($and);

        return $qb->getQuery()->getResult();
    }

    public function findEventsFromYearOrderedByMonth(SearchEvent $data, $year, $limit = null)
    {
        $dateFrom = new \DateTime($year . '-01-01');
        $dateTo = new \DateTime($year . '-01-01');
        //TODO y si es un año bisiesto perdemos lo que pase en fin de año?
        $dateTo->modify('+365 days');

        $qb = $this->createQueryBuilder('e');
        $qb->select('eventDate.date as date, COUNT(e.id) as events');
        $qb->leftJoin('e.eventDates', 'eventDate');
        $qb->addOrderBy('eventDate.date','ASC');
        $qb->addGroupBy('eventDate.date');

        $and = $qb->expr()->andx();

        if ($data->getCategory() != null) {
            $qb->leftJoin('e.category', 'categories');
            $and->add($qb->expr()->in('categories.slug', '\''. $data->getCategory() .'\''));
        }

        if ($data->getTag() != null) {
            $qb->leftJoin('e.tags', 'tags');
            $and->add($qb->expr()->in('tags.slug', '\''. $data->getTag() .'\''));
        }

        if ($data->getProvince() != null) {
            $qb->leftJoin('e.city', 'city');
            $qb->leftJoin('city.province', 'province');
            $and->add($qb->expr()->in('province.slug', '\''. $data->getProvince() .'\''));
            if ($data->getCity() != null) {
                $and->add($qb->expr()->in('city.slug', '\''. $data->getCity() .'\''));
            }
        }

        $and->add($qb->expr()->gte('eventDate.date', '\''.$dateFrom->format('Y-m-d').'\''));
        $and->add($qb->expr()->lt('eventDate.date', '\''.$dateTo->format('Y-m-d').'\''));

        $and->add($qb->expr()->isNotNull('e.published'));
        $qb->where($and);

        if (isset($limit)) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getScalarResult();
    }

    public function findEventsDateOrderByMonth(SearchEvent $data, $year, $limit = null)
    {
        $dateFrom = new \DateTime($year . '-01-01');
        $dateTo = new \DateTime($year . '-01-01');
        $dateTo->modify('+365 days');

        $qb = $this->createQueryBuilder('e');
        $qb->select('eventDate.date as date, COUNT(e.id) as events');

        $qb->leftJoin('e.eventDates', 'eventDate');

        $qb->addOrderBy('eventDate.date','ASC');
        $qb->addGroupBy('eventDate.date');

        $and = $qb->expr()->andx();

        if ($data->getCategory() != null) {
            $qb->leftJoin('e.category', 'categories');
            $and->add($qb->expr()->in('categories.slug', '\''. $data->getCategory() .'\''));
        }

        if ($data->getTag()!=null) {
            $qb->leftJoin('e.tags', 'tags');
            $and->add($qb->expr()->in('tags.slug', '\''. $data->getTag() .'\''));
        }

        if ($data->getProvince() != null) {
            $qb->leftJoin('e.city', 'city');
            $qb->leftJoin('city.province', 'province');
            $and->add($qb->expr()->in('province.slug', '\''. $data->getProvince() .'\''));
            if ($data->getCity() != null) {
                $and->add($qb->expr()->in('city.slug', '\''. $data->getCity() .'\''));
            }
        }

        $and->add($qb->expr()->gte('eventDate.date', '\''.$dateFrom->format('Y-m-d').'\''));
        $and->add($qb->expr()->lt('eventDate.date', '\''.$dateTo->format('Y-m-d').'\''));

        $and->add($qb->expr()->isNotNull('e.published'));
        $qb->where($and);

        if (isset($limit)) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getScalarResult();
    }

    public function findRelatedEventsFromTags($event, $limit = null)
    {
        $qb = $this->createQueryBuilder('e');

        $qb->select('e');
        $qb->leftJoin('e.category', 'cat');
        $or = $qb->expr()->orx();
        $and = $qb->expr()->andx();
        $and->add($qb->expr()->isNotNull('e.published'));
        $and->add($qb->expr()->neq('e.slug', '\'' . $event->getSlug() . '\''));
        $or->add($qb->expr()->in('cat.slug', '\''. $event->getCategory()->getslug() .'\''));

        $qb->where($and->add($or));

        if (isset($limit))
            $qb->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function findEventByDate($date, $search = null, $limit = null)
    {
        $qb = $this->createQueryBuilder('e');

        if (is_string($date)) {
            $date = new \DateTime($date);
        }

        $qb->select('e');
        $qb->leftJoin('e.eventDates', 'eventDate');
        $qb->addOrderBy('eventDate', 'ASC');
        $and = $qb->expr()->andx();
        $and->add($qb->expr()->isNotNull('e.published'));

        if (isset($search['tag']) && $search['tag'] != "") {
            $qb->leftJoin('e.tags', 'tags');
            $and->add($qb->expr()->eq('tags.slug', '\'' . $search['tag'] . '\''));
        }

        if (isset($search['province']) && $search['province'] != "") {
            $qb->leftJoin('e.city', 'city');
            $qb->leftJoin('city.province', 'province');
            $and->add($qb->expr()->eq('province.slug', '\'' . $search['province'] . '\''));

            if (isset($search['city']) && $search['city'] != "") {
                $and->add($qb->expr()->eq('city.slug', '\'' . $search['city'] . '\''));
            }
        }

        $and->add($qb->expr()->eq('eventDate.date', '\''.$date->format('Y-m-d H:i:s').'\''));

        $qb->where($and);

        if (isset($limit))
            $qb->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param FormularioBusquedaAux $data
     *
     * @return \Doctrine\ORM\Query
     */
    public function findEventDQLSEO(SearchEvent $data, $limit = null)
    {
        $qb = $this->createQueryBuilder('s');
        $and = $qb->expr()->andx();
        $qb->select('s', 'ciu', 'p');

        $qb->leftJoin('s.city', 'ciu');
        $qb->leftJoin('ciu.province', 'p');
        $qb->addOrderBy('s.id','DESC');

        $and = $qb->expr()->andx();
        if ($data->getProvince()!=null) {
            $and->add($qb->expr()->eq('p.slug', '\''. $data->getProvince().'\''));
        }

        if ($data->getCategory()!=null) {
            $qb->leftJoin('s.category', 'cat');
            $and->add($qb->expr()->eq('cat.slug', '\''. $data->getCategory() .'\''));
        }

        if ($data->getCity()) {
            $and->add($qb->expr()->eq('ciu.slug', '\''. $data->getCity() .'\''));
        }

        $and->add($qb->expr()->isNotNull('s.published'));
        $qb->where($and);

        if (isset($limit)) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param FormularioBusquedaAux $data
     *
     * @return \Doctrine\ORM\Query
     */
    public function getCategoriesWithMoreEvents($year, $limit = null)
    {
        $qb = $this->createQueryBuilder('i');
        $and = $qb->expr()->andx();
        $qb->select('c.name, c.slug, COUNT(DISTINCT i) as numberOfEvents');

        $qb->leftJoin('i.category', 'c');
        $qb->addOrderBy('numberOfEvents', 'DESC');
        $qb->leftJoin('i.eventDates', 'eventDate');

        $date = '01-01-' . $year;
        $dateFrom = new \DateTime($date);
        $dateTo = new \DateTime($date);
        $dateTo->modify('+1 year');

        $and->add($qb->expr()->gte('eventDate.date', '\''.$dateFrom->format('Y-m-d H:i:s').'\''));
        $and->add($qb->expr()->lt('eventDate.date', '\''.$dateTo->format('Y-m-d H:i:s').'\''));
        $and->add($qb->expr()->isNotNull('i.published'));
        $qb->where($and);
        $qb->groupBy('c');

        if (isset($limit)) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param FormularioBusquedaAux $data
     *
     * @return \Doctrine\ORM\Query
     */
    public function getProvincesWithMoreEvents($year, $limit = null)
    {
        $qb = $this->createQueryBuilder('i');
        $qb->select('p.name, p.slug, COUNT(DISTINCT i) as numberOfEvents');

        $qb->leftJoin('i.city', 'c');
        $qb->leftJoin('c.province', 'p');
        $qb->leftJoin('i.eventDates', 'eventDate');
        $qb->addOrderBy('numberOfEvents', 'DESC');

        $date = '01-01-' . $year;
        $dateFrom = new \DateTime($date);
        $dateTo = new \DateTime($date);
        $dateTo->modify('+1 year');
        $and = $qb->expr()->andx();
        $and->add($qb->expr()->gte('eventDate.date', '\''.$dateFrom->format('Y-m-d H:i:s').'\''));
        $and->add($qb->expr()->lt('eventDate.date', '\''.$dateTo->format('Y-m-d H:i:s').'\''));
        $and->add($qb->expr()->isNotNull('i.published'));
        $qb->where($and);
        $qb->groupBy('p');

        if (isset($limit)) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param FormularioBusquedaAux $data
     *
     * @return \Doctrine\ORM\Query
     */
    public function getCitiesWithMoreEvents($year, $limit = null)
    {
        $qb = $this->createQueryBuilder('i');
        $and = $qb->expr()->andx();
        $or = $qb->expr()->orx();
        $qb->select('c.name, c.slug, COUNT(DISTINCT i) as numberOfEvents');

        $qb->leftJoin('i.city', 'c');
        $qb->leftJoin('i.eventDates', 'eventDate');
        $qb->addOrderBy('numberOfEvents', 'DESC');

        $date = '01-01-' . $year;
        $dateFrom = new \DateTime($date);
        $dateTo = new \DateTime($date);
        $dateTo->modify('+1 year');
        $and = $qb->expr()->andx();

        $and->add($qb->expr()->gte('eventDate.date', '\''.$dateFrom->format('Y-m-d H:i:s').'\''));
        $and->add($qb->expr()->lt('eventDate.date', '\''.$dateTo->format('Y-m-d H:i:s').'\''));
        $and->add($qb->expr()->isNotNull('i.published'));
        $qb->where($and);
        $qb->groupBy('c');

        if (isset($limit)) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param FormularioBusquedaAux $data
     *
     * @return \Doctrine\ORM\Query
     */
    public function getEventsWithProvinceFromCategory($categorySlug, $year, $limit = null)
    {
        $qb = $this->createQueryBuilder('i');
        $and = $qb->expr()->andx();
        $or = $qb->expr()->orx();
        $qb->select('p.name, p.slug, COUNT(DISTINCT i) as numberOfEvents');

        $qb->leftJoin('i.category', 'c');
        $qb->leftJoin('i.city', 'ci');
        $qb->leftJoin('ci.province', 'p');
        $qb->leftJoin('i.eventDates', 'eventDate');
        $qb->addOrderBy('numberOfEvents', 'DESC');

        $and->add($qb->expr()->eq('c.slug', '\'' . $categorySlug . '\''));
        $date = '01-01-' . $year;
        $dateFrom = new \DateTime($date);
        $dateTo = new \DateTime($date);
        $dateTo->modify('+1 year');

        $and->add($qb->expr()->gte('eventDate.date', '\''.$dateFrom->format('Y-m-d H:i:s').'\''));
        $and->add($qb->expr()->lt('eventDate.date', '\''.$dateTo->format('Y-m-d H:i:s').'\''));
        $and->add($qb->expr()->isNotNull('i.published'));
        $qb->where($and);
        $qb->groupBy('p');

        if (isset($limit)) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param FormularioBusquedaAux $data
     *
     * @return \Doctrine\ORM\Query
     */
    public function getEventsWithCityFromCategory($categorySlug, $year, $limit = null)
    {
        $qb = $this->createQueryBuilder('i');
        $and = $qb->expr()->andx();
        $or = $qb->expr()->orx();
        $qb->select('ci.name, ci.slug, COUNT(DISTINCT i) as numberOfEvents');

        $qb->leftJoin('i.category', 'c');
        $qb->leftJoin('i.city', 'ci');
        $qb->leftJoin('i.eventDates', 'eventDate');
        $qb->addOrderBy('numberOfEvents', 'DESC');

        $and->add($qb->expr()->eq('c.slug', '\'' . $categorySlug . '\''));
        $date = '01-01-' . $year;
        $dateFrom = new \DateTime($date);
        $dateTo = new \DateTime($date);
        $dateTo->modify('+1 year');

        $and->add($qb->expr()->gte('eventDate.date', '\''.$dateFrom->format('Y-m-d H:i:s').'\''));
        $and->add($qb->expr()->lt('eventDate.date', '\''.$dateTo->format('Y-m-d H:i:s').'\''));
        $and->add($qb->expr()->isNotNull('i.published'));
        $qb->where($and);
        $qb->groupBy('ci');

        if (isset($limit)) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param FormularioBusquedaAux $data
     *
     * @return \Doctrine\ORM\Query
     */
    public function getEventsWithCityFromCategoryAndProvince($provinceSlug, $categorySlug, $citySlug = null, $year, $limit = null)
    {
        $qb = $this->createQueryBuilder('i');
        $and = $qb->expr()->andx();
        $or = $qb->expr()->orx();
        $qb->select('ci.name, ci.slug, COUNT(DISTINCT i) as numberOfEvents');

        $qb->leftJoin('i.category', 'c');
        $qb->leftJoin('i.city', 'ci');
        $qb->leftJoin('ci.province', 'p');
        $qb->leftJoin('i.eventDates', 'eventDate');
        $qb->addOrderBy('numberOfEvents', 'DESC');

        $and->add($qb->expr()->eq('c.slug', '\'' . $categorySlug . '\''));
        $and->add($qb->expr()->eq('p.slug', '\'' . $provinceSlug . '\''));
        if (isset($citySlug))
            $and->add($qb->expr()->neq('ci.slug', '\'' . $citySlug . '\''));
        $date = '01-01-' . $year;
        $dateFrom = new \DateTime($date);
        $dateTo = new \DateTime($date);
        $dateTo->modify('+1 year');

        $and->add($qb->expr()->gte('eventDate.date', '\''.$dateFrom->format('Y-m-d H:i:s').'\''));
        $and->add($qb->expr()->lt('eventDate.date', '\''.$dateTo->format('Y-m-d H:i:s').'\''));
        $and->add($qb->expr()->isNotNull('i.published'));
        $qb->where($and);
        $qb->groupBy('ci');

        if (isset($limit)) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param FormularioBusquedaAux $data
     *
     * @return \Doctrine\ORM\Query
     */
    public function getEventsWithCategoryFromProvince($provinceSlug, $citySlug = null, $year, $limit = null)
    {
        $qb = $this->createQueryBuilder('i');
        $and = $qb->expr()->andx();
        $or = $qb->expr()->orx();
        $qb->select('c.name, c.slug, COUNT(DISTINCT i) as numberOfEvents');

        $qb->leftJoin('i.category', 'c');
        $qb->leftJoin('i.city', 'ci');
        $qb->leftJoin('ci.province', 'p');
        $qb->leftJoin('i.eventDates', 'eventDate');
        $qb->addOrderBy('numberOfEvents', 'DESC');

        $and->add($qb->expr()->eq('p.slug', '\'' . $provinceSlug . '\''));
        if (isset($citySlug))
            $and->add($qb->expr()->eq('ci.slug', '\'' . $citySlug . '\''));
        $date = '01-01-' . $year;
        $dateFrom = new \DateTime($date);
        $dateTo = new \DateTime($date);
        $dateTo->modify('+1 year');

        $and->add($qb->expr()->gte('eventDate.date', '\''.$dateFrom->format('Y-m-d H:i:s').'\''));
        $and->add($qb->expr()->lt('eventDate.date', '\''.$dateTo->format('Y-m-d H:i:s').'\''));
        $and->add($qb->expr()->isNotNull('i.published'));
        $qb->where($and);
        $qb->groupBy('c');

        if (isset($limit)) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param FormularioBusquedaAux $data
     *
     * @return \Doctrine\ORM\Query
     */
    public function getEventsWithCityFromProvince($provinceSlug, $citySlug = null, $year, $limit = null)
    {
        $qb = $this->createQueryBuilder('i');
        $and = $qb->expr()->andx();
        $or = $qb->expr()->orx();
        $qb->select('ci.name, ci.slug, COUNT(DISTINCT i) as numberOfEvents');

        $qb->leftJoin('i.city', 'ci');
        $qb->leftJoin('ci.province', 'p');
        $qb->leftJoin('i.eventDates', 'eventDate');
        $qb->addOrderBy('numberOfEvents', 'DESC');

        $and->add($qb->expr()->eq('p.slug', '\'' . $provinceSlug . '\''));
        if (isset($citySlug))
            $and->add($qb->expr()->neq('ci.slug', '\'' . $citySlug . '\''));
        $date = '01-01-' . $year;
        $dateFrom = new \DateTime($date);
        $dateTo = new \DateTime($date);
        $dateTo->modify('+1 year');

        $and->add($qb->expr()->gte('eventDate.date', '\''.$dateFrom->format('Y-m-d H:i:s').'\''));
        $and->add($qb->expr()->lt('eventDate.date', '\''.$dateTo->format('Y-m-d H:i:s').'\''));
        $and->add($qb->expr()->isNotNull('i.published'));
        $qb->where($and);
        $qb->groupBy('ci');

        if (isset($limit)) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}