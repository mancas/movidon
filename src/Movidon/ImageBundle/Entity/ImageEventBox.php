<?php
namespace Movidon\ImageBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 *
 * @ORM\Table()
 * @ORM\Entity()
 */
class ImageEventBox extends ImageCopy
{
    protected $maxWidth = 358;
    protected $maxHeight = 225;
    protected $sufix = "box";
    protected $crop = true;
}