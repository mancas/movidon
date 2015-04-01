<?php
namespace Movidon\ImageBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 *
 * @ORM\Table()
 * @ORM\Entity()
 */
class ImageEventCalendarThumbnail extends ImageCopy
{
    protected $maxWidth = 64;
    protected $maxHeight = 64;
    protected $sufix = "calendar-thumb";
    protected $crop = false;
}