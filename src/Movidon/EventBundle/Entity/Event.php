<?php

namespace Movidon\EventBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Movidon\ImageBundle\Entity\ImageEvent;
use Movidon\LocationBundle\Entity\City;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Movidon\ImageBundle\Entity\Image;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Movidon\EventBundle\Entity\EventRepository")
 */
class Event
{
    const RELATED_ITEMS_LIMIT = 10;
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     */
    protected $updated;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    protected $created;

    /**
     * @ORM\Column(name="dateOfEvent", type="date", nullable=true)
     */
    protected $dateOfEvent;

    /**
     * @ORM\Column(name="published", type="date", nullable=true)
     */
    protected $published;

    /**
     * @ORM\Column(name="content", type="text")
     */
    protected $content;

    /**
     * @ORM\Column(name="title", type="text")
     */
    protected $title;

    /**
     * @Gedmo\Slug(fields={"title"}, updatable=false)
     * @ORM\Column(name="slug", type="string", length=255, nullable=true)
     */
    protected $slug;

    /**
     * Image
     *
     * @ORM\OneToOne(targetEntity="Movidon\ImageBundle\Entity\ImageEvent", mappedBy="event", cascade={"persist", "merge", "remove"})
     * @ORM\OrderBy({"main" = "DESC"})
     */
    private $image;

    /**
     * @var City $city
     * @ORM\ManyToOne(targetEntity="Movidon\LocationBundle\Entity\City")
     */
    protected $city;

    /**
     * @ORM\ManyToMany(targetEntity="Movidon\EventBundle\Entity\Tag", inversedBy="events")
     * @ORM\JoinTable(name="event_tag")
     */
    protected $tags;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \DateTime $published
     */
    public function setPublished($published)
    {
        $this->published = $published;
    }

    /**
     * @return \DateTime
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * @param \DateTime $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return ImageEvent
     */
    public function getImage()
    {
        return $this->image;
    }

    public function setImage(ImageEvent $image)
    {
        $this->image = $image;
    }

    public function getImageCopy($type)
    {
        if ($this->getImage()) {
            return $this->getImage()->getImageCopyFromType($type)
                ->getWebFilePath();
        }

        return Image::DEFAULT_IMG_W;
    }

    /**
     * @return City $city
     */
    public function getCity()
    {
        return $this->city;
    }

    public function setCity(City $city)
    {
        $this->city = $city;
    }

    public function getFormatDate($format = null)
    {
        $date = $this->dateOfEvent;
        if (isset($format)) {
            $date = $date->format($format);
        } else {
            $date = $date->format('Y-m-d');
        }

        return $date;
    }

    /**
     * @return mixed
     */
    public function getDateOfEvent()
    {
        return $this->dateOfEvent;
    }

    /**
     * @param mixed $dateOfEvent
     */
    public function setDateOfEvent($dateOfEvent)
    {
        $this->dateOfEvent = $dateOfEvent;
    }

    /**
     * @param Tag $tags
     */
    public function addTag(Tag $tag)
    {
        $this->tags->add($tag);
    }

    /**
     * @param Tag $tags
     */
    public function removeTag(Tag $tag)
    {
        $this->tags->removeElement($tag);
    }

    public function removeTags()
    {
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTags()
    {
        return $this->tags;
    }

    public function getTagsAsString()
    {
        $getName = function ($tag) {
            return $tag->getName();
        };

        return join(" - ", array_map($getName, $this->tags->toArray()));
    }
}
