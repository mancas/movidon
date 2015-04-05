<?php

namespace Movidon\ImageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Movidon\ImageBundle\Util\FileHelper;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * @ORM\Table()
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"event"="ImageEvent"})
 */
abstract class Image
{
    const WEB_PATH = 'bundles/frontend/img/';
    const UPLOAD_PATH = '/var/www/html/movidon/web/uploads/';
    const ERROR_MESSAGE = "Ha ocurrido un error. Asegúrate de subir imágenes JPG o PNG de menos de 2 megas y mayores a 640x480.";
    const INFO_MESSAGE = "El formato de las imágenes ha de ser JPG o PNG, deben pesar menos de 2 megas y ser mayores a 640x480.";
    const AJAX_LOADER = 'bundles/frontend/img/ajax-loader.gif';
    const DEFAULT_IMG = 'bundles/frontend/img/no-photo-space.png';
    const DEFAULT_IMG_W = 'bundles/frontend/img/no-photo-spacew.png';
    const DEFAULT_IMG_D = 'bundles/frontend/img/no-photo-spaced.png';
    const UPLOAD_DIR = 'uploads';

    const MIN_WIDTH = 640;
    const MIN_HEIGHT = 480;
    const MAX_SIZE = '2M';

    protected $subdirectory = "images";
    /**
     * @Assert\File(maxSize = "2M", mimeTypes = {"image/png", "image/jpg", "image/jpeg"})
     */
    protected $file;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date()
     */
    protected $createDate;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated", type="date", nullable=true)
     * @Assert\Date()
     */
    protected $updateDate;

    /**
     * @ORM\Column(name="dateRemove", type="date", nullable=true)
     */
    protected $deletedDate;

    /**
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    protected $image;

    /**
     * @ORM\Column(name="description", type="string", length=300, nullable=true)
     */
    protected $description;

    /**
     * @ORM\OneToMany(targetEntity="Movidon\ImageBundle\Entity\ImageCopy", mappedBy="image", cascade={"persist", "merge", "remove"})
     */
    protected $imageCopies;

    protected $maxWidth = 1024;

    protected $maxHeight = 768;

    protected $quality = 70;

    protected $crop = false;

    public function __construct()
    {
        $this->imageCopies = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCreateDate()
    {
        return $this->createDate;
    }

    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setDeletedDate($dateRemove)
    {
        $this->deletedDate = $dateRemove;
    }

    public function getDeletedDate()
    {
        return $this->deletedDate;
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file)
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

    public function getSubdirectory()
    {
        return $this->subdirectory . '/' . FileHelper::getDirectoryNameFromId($this->id);
    }

    public function getWebFilePath()
    {
        return self::UPLOAD_DIR . '/' . $this->getSubdirectory() . '/' . $this->image;
    }

    public function uploadImage()
    {
        $nameImage = FileHelper::uploadAndReplaceFile(null, $this->file,
            $this->subdirectory, $this->id);
        $this->image = $nameImage;
        $this->saveResizedImage();
    }

    public function saveResizedImage()
    {
        $originalFilePath = self::UPLOAD_PATH . $this->getSubdirectory() . '/'
            . $this->image;
        $dimensions = $this->getImageDimensions($originalFilePath);
        $this->setMaxWidth($dimensions[0]);
        $this->setMaxHeight($dimensions[1]);
        FileHelper::executeConvert($originalFilePath, $originalFilePath,
            $dimensions[0], $dimensions[1], $this->quality, $this->crop);
    }

    protected function getImageDimensions($originalFilePath = null)
    {
        return array($this->maxWidth, $this->maxHeight);
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreateDate()
    {
        $this->createDate = new \DateTime('today');
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdateDate()
    {
        $this->updateDate = new \DateTime('today');
    }

    public function getImageThumbnail()
    {
        return $this->getImageCopyFromType('ImageThumbnail');
    }

    public function setImageThumbnail(\Movidon\ImageBundle\Entity\ImageThumbnail $thumbnail)
    {
        $this->setUniqueImageCopy($thumbnail);
    }

    public function getImageNav()
    {
        return $this->getImageCopyFromType('ImageNav');
    }

    public function setImageNav(\Movidon\ImageBundle\Entity\ImageNav $nav)
    {
        $this->setUniqueImageCopy($nav);
    }

    public function setUniqueImageCopy($imageCopy)
    {
        foreach ($this->imageCopies as $imgc) {
            if (get_class($imgc) == get_class($imageCopy)) {
                $this->imageCopies->removeElement($imgc);
                break;
            }
        }

        $this->imageCopies->add($imageCopy);
    }

    public function getImageCopies()
    {
        return $this->imageCopies;
    }

    /**
     * @param $type
     * @return ImageCopy
     */
    public function getImageCopyFromType($type)
    {
        $class = "Movidon\\ImageBundle\\Entity\\$type";
        foreach ($this->imageCopies as $img) {
            if (get_class($img) == $class) {
                return $img;
            }
        }

        return null;
    }

    public function createCopies()
    {
        $oldRoute = null;
        if ($this->getImage()) {
            $oldRoute = $this->getImage();
            $thumb = $this->getImageThumbnail();
            if (!$thumb) {
                $thumb = new ImageThumbnail();
            }
        } else {
            $thumb = new ImageThumbnail();
        }
        $copies = array($thumb);

        return array($oldRoute, $copies);
    }

    public function createNav()
    {
        $nav = $this->getImageNav();
        if (!$nav) {
            $nav = new ImageNav();
        }

        return $nav;
    }

    public function uploadNewCopies($copies)
    {
        foreach ($copies as $copy) {
            $copy->setImage($this);
            $copy->uploadCopy();
            $this->setUniqueImageCopy($copy);
        }
    }

}
