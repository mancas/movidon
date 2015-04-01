<?php
namespace Movidon\ImageBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 *
 * @ORM\Table()
 * @ORM\Entity()
 */
class ImageEventCalendar extends ImageCopy
{
    protected $maxWidth = 720;
    protected $maxHeight = 480;
    protected $sufix = "calendar";
    protected $crop = false;
}