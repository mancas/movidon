<?php
namespace Movidon\ImageBundle\Entity;
use Doctrine\Tests\DBAL\Types\IntegerTest;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 *
 * @ORM\Table()
 * @ORM\Entity()
 */
class ImageThumbnail extends ImageCopy
{
    protected $maxWidth = 170;
    protected $maxHeight = 240;
    protected $sufix = "thumb";
    protected $crop = false;
}