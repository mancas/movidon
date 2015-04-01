<?php

namespace Movidon\ImageBundle\Entity;

use Movidon\ImageBundle\Entity\Image;
use Movidon\ImageBundle\Util\FileHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class ImageEvent extends Image
{
    CONST MAX_WIDTH = 1024;
    CONST MAX_HEIGHT = 768;
    protected $subdirectory = "images/events";
    protected $maxWidth = self::MAX_WIDTH;
    protected $maxHeight = self::MAX_HEIGHT;

    /**
     * @ORM\ManyToOne(targetEntity="Movidon\EventBundle\Entity\Event", inversedBy="images")
     */
    protected $event;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default" = 0})
     */
    protected $main = false;

    public function setEvent(\Movidon\EventBundle\Entity\Event $event)
    {
        $this->event = $event;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function uploadImage()
    {
        $nameImage = FileHelper::uploadAndReplaceFile($this->image, $this->file, $this->subdirectory, $this->id);
        $this->image = $nameImage;
        $this->saveResizedImage();
    }

    public function createCopies()
    {
        list($oldRoute, $copies) = parent::createCopies();
        $copies[] = $this->createImageEventBox();
        $copies[] = $this->createImageEventBoxW();
        $copies[] = $this->createImageEventCalendar();
        $copies[] = $this->createImageEventCalendarThumbnail();

        return array($oldRoute, $copies);
    }

    protected function createImageEventBox()
    {
        $copy = $this->getImageEventBox();
        if (!$copy) {
            $copy = new ImageEventBox();
        }

        return $copy;
    }

    protected function createImageEventBoxW()
    {
        $copy = $this->getImageEventBox();
        if (!$copy) {
            $copy = new ImageEventBoxW();
        }

        return $copy;
    }

    protected function createImageEventCalendar()
    {
        $copy = $this->getImageEventCalendar();
        if (!$copy) {
            $copy = new ImageEventCalendar();
        }

        return $copy;
    }

    protected function createImageEventCalendarThumbnail()
    {
        $copy = $this->getImageEventCalendarThumbnail();
        if (!$copy) {
            $copy = new ImageEventCalendarThumbnail();
        }

        return $copy;
    }

    public function getImageEventBox()
    {
        return $this->getImageCopyFromType('ImageEventBox');
    }

    public function getImageEventBoxW()
    {
        return $this->getImageCopyFromType('ImageEventBoxW');
    }

    public function getImageEventCalendar()
    {
        return $this->getImageCopyFromType('ImageEventCalendar');
    }

    public function getImageEventCalendarThumbnail()
    {
        return $this->getImageCopyFromType('ImageEventCalendarThumbnail');
    }

    public function setImageEventCalendarThumbnail(\Movidon\ImageBundle\Entity\ImageEventCalendarThumbnail $thumbnail)
    {
        $this->setUniqueImageCopy($thumbnail);
    }
    public function setImageEventCalendar(\Movidon\ImageBundle\Entity\ImageEventCalendar $imageCalendar)
    {
        $this->setUniqueImageCopy($imageCalendar);
    }
    public function setImageEventBox(\Movidon\ImageBundle\Entity\ImageEventBox $imageCalendar)
    {
        $this->setUniqueImageCopy($imageCalendar);
    }

}
