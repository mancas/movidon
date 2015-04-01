<?php

namespace Movidon\ImageBundle\Entity;
use Doctrine\Tests\DBAL\Types\IntegerTest;
use Movidon\ImageBundle\Util\FileHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="subclase", type="string")
 * @ORM\DiscriminatorMap({"imagethumbnail"="ImageThumbnail", "imageeventbox"="ImageEventBox",
 * "imageeventboxw"="ImageEventBoxW", "imageeventcalendar"="ImageEventCalendar", "imageeventcalendarthumbnail"="ImageEventCalendarThumbnail"})
 */
abstract class ImageCopy
{
    protected $maxWidth;
    protected $maxHeight;
    protected $sufix;
    protected $width = null;
    protected $height = null;

    /**
     * @Assert\Image(maxSize="6000000")
     */
    protected $file;

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $image
     *
     * @ORM\Column(name="imageName", type="string", length=255, nullable=true)
     */
    protected $imageName;

    /**
     * @ORM\ManyToOne(targetEntity="Movidon\ImageBundle\Entity\Image", inversedBy="imageCopies")
     */
    protected $image;

    /**
     * @ORM\Column(name="dateRemove", type="date", nullable=true)
     */
    protected $dateRemove;

    public function getId()
    {
        return $this->id;
    }

    public function setImageName($image)
    {
        $this->imageName = $image;
    }

    public function getImageName()
    {
        return $this->imageName;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage(Image $image)
    {
        $this->image = $image;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function setMaxWidth($width)
    {
        $this->maxWidth = $width;
    }

    public function getMaxWidth()
    {
        return $this->maxWidth;
    }

    public function setMaxHeight($height)
    {
        $this->height = $height;
    }

    public function getMaxHeight()
    {
        return $this->maxHeight;
    }

    public function setDateRemove($dateRemove)
    {
        $this->dateRemove = $dateRemove;
    }

    public function getDateRemove()
    {
        return $this->dateRemove;
    }

    public function uploadCopy($maximize=false)
    {
        $image = $this->image->getImage();
        $directory = FileHelper::getDirectoryNameFromId($this->image->getId());
        $filePath = Image::UPLOAD_PATH . $this->image->getSubdirectory();
        $thumb = FileHelper::resizeAndSaveImage($image, $this->maxWidth, $this->maxHeight, $filePath, $this->sufix, $this->crop);
        $imageName = FileHelper::generateThumbFileName($image, $this->sufix);
        $this->setImageName($imageName);
    }

    public function getSubdirectory()
    {
        return $this->image->getSubdirectory();
    }

    public function getWebFilePath()
    {
        return Image::UPLOAD_DIR . '/' . $this->getSubdirectory() . '/' . $this->imageName;
    }

    public function getWidth()
    {
        if (!$this->width) {
            $this->width = FileHelper::getWidthFromWebFile($this->getWebFilePath());
        }

        return $this->width;
    }

    public function getHeight()
    {
        if (!$this->height) {
            $this->height = FileHelper::getHeightFromWebFile($this->getWebFilePath());
        }

        return $this->height;
    }

    /** @ORM\PreRemove */
    public function removeImage()
    {
        @unlink(Image::UPLOAD_PATH.$this->getWebFilePath());
    }
}