<?php

namespace Movidon\LocationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class City
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $name;

    /**
     * @Gedmo\Slug(fields={"name"}, updatable=false)
     * @ORM\Column(name="slug", type="string", length=255)
     */
    protected $slug;

    /**
     * @var Province $province
     * @ORM\ManyToOne(targetEntity="Movidon\LocationBundle\Entity\Province")
     */
    protected $province;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
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
     * @return Province $province
     */
    public function getProvince()
    {
        return $this->province;
    }

    public function setProvince(Province $province)
    {
        $this->province = $province;
    }

    public static function mainCitiesIds()
    {
        return array(6148, 4354, 881, 4521, 2063, 7484, 7214, 2745, 8108);
    }
}
