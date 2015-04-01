<?php
namespace Movidon\EventBundle\Util\Filter;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EventFilter
{
    private $categorySlug;
    private $tagSlug;
    private $city;
    private $province;
    private $date;
    private $page = 1;

    public static function createFromSpec($eventFilterSpec)
    {
        $filter = new EventFilter();

        if (!preg_match(
                '{^
                (/c/(?P<categorySlug>[^/]+))?
                (/t/(?P<tagSlug>[^/]+))?
                (/p/(?P<province>[^/]+))?
                (/l/(?P<city>[^/]+))?
                (/d/(?P<date>[^/]+))?
                (/g/(?P<page>[1-9][0-9]*))?
                $}x', '/' . $eventFilterSpec, $matches)) {
            throw new HttpException(404, "Bad url $eventFilterSpec");
        }

        foreach (array('categorySlug', 'tagSlug', 'province', 'city', 'date') as $v) {
            if (isset($matches[$v])) {
                $filter->$v = $matches[$v];
            }
        }

        if (isset($matches['page'])) {
            $filter->page = $matches['page'];
            if ($filter->page <= 1) {
                throw new HttpException(404,
                        "Page number must be bigger than one. It is {$filter
                                ->page}.");
            }
        }

        return $filter;
    }

    public static function createFromObjects($category = null, $tag = null,
            $province = null, $city = null, $date = null, $page = 1)
    {
        $filter = new EventFilter();
        if ($category) {
            $filter->categorySlug = $category->getSlug();
        }

        if ($tag) {
            $filter->tagSlug = $tag->getSlug();
        }

        if ($city) {
            $filter->city = $city->getSlug();
        } else if ($province) {
            $filter->province = $province->getSlug();
        }

        if ($date) {
            $filter->date = $date;
        }

        if ($page > 1) {
            $filter->page = $page;
        }

        return $filter;
    }

    public static function createFromStrings($category = null, $tag = null,
            $province = null, $city = null, $date = null, $page = 1)
    {
        $filter = new EventFilter();
        if ($category) {
            $filter->categorySlug = $category;
        }

        if ($tag) {
            $filter->tagSlug = $tag;
        }

        if ($city) {
            $filter->city = $city;
        } else if ($province) {
            $filter->province = $province;
        }

        if ($date) {
            $filter->date = $date;
        }

        if ($page > 1) {
            $filter->page = $page;
        }

        return $filter;
    }

    public function getSpec()
    {
        $pieces = array();

        if ($this->categorySlug) {
            $pieces[] = 'c/' . $this->categorySlug;
        }

        if ($this->tagSlug) {
            $pieces[] = 't/' . $this->tagSlug;
        }

        if ($this->city) {
            $pieces[] = 'l/' . $this->city;
        } else if ($this->province) {
            $pieces[] = 'p/' . $this->province;
        }

        if ($this->date) {
            $pieces[] = 'd/' . $this->date;
        }

        if ($this->page > 1) {
            $pieces[] = 'g/' . $this->getPage();
        }

        return join('/', $pieces);
    }

    public static function constructSpec($category = null, $tag = null,
            $province = null, $city = null, $neighborhood = null, $from = null,
            $to = null, $date = null, $page = 1)
    {
        if (is_string($category) || is_string($tag) || is_string($province)
                || is_string($city) || is_string($date)) {
            $serviceFilter = self::createFromStrings($category, $tag,
                    $province, $city, $date, $page);
        } else {
            $serviceFilter = self::createFromObjects($category, $tag,
                    $province, $city, $date, $page);
        }

        return $serviceFilter->getSpec();
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $val)
    {
        $this->$name = $val;
    }

    public function getTagSlug()
    {
        return $this->tagSlug;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function getProvince()
    {
        return $this->province;
    }

    public function getCategorySlug()
    {
        return $this->categorySlug;
    }

    public function getDate()
    {
        return $this->date;
    }
}
