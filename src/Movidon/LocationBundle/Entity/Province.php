<?php

namespace Movidon\LocationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table()
 *  @ORM\Entity()
 */
class Province
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string $slug
     * @Gedmo\Slug(fields={"name"}, updatable=false)
     * @ORM\Column(name="slug", type="string", length=255)
     */
    protected $slug;

    /**
     * @var Country $country
     * @ORM\ManyToOne(targetEntity="Movidon\LocationBundle\Entity\Country")
     */
    protected $country;

    public function getId()
    {
        return $this->id;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return Country $country
     */
    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry(Country $country)
    {
        $this->country = $country;
    }

}
