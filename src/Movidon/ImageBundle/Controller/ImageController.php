<?php

namespace Movidon\ImageBundle\Controller;

use Movidon\FrontendBundle\Controller\CustomController;
use Movidon\FrontendBundle\Util\FunctionsHelper;
use Movidon\ImageBundle\Util\FileHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Movidon\ImageBundle\Entity\ImageItem;
use Movidon\ImageBundle\Entity\ImageCopy;
use Movidon\ImageBundle\Entity\Image;
use Symfony\Component\HttpFoundation\Request;

class ImageController extends CustomController
{
    /**
     * @ParamConverter("imageItem", class="ImageBundle:ImageItem")
     */
    public function deleteImageAction(ImageItem $imageItem, Request $request)
    {
        $jsonResponse = json_encode(array('ok' => false));
        if ($request->isXmlHttpRequest()) {
            $em = $this->getEntityManager();

            if (!$imageItem) {
                return $this->noPermission();
            }
            foreach ($imageItem->getImageCopies() as $copy)
            {
                $copy->setDateRemove(new \DateTime('now'));
                $em->flush();
                FileHelper::removeFileFromDirectory($copy->getImageName(), $copy->getSubdirectory());
            }
            $imageItem->setDeletedDate(new \DateTime('now'));
            if (FunctionsHelper::isClass($imageItem, "imageItem") && $imageItem->getMain()) {
                $imageItem->setMain(false);
            }
            $em->flush();
            FileHelper::removeFileFromDirectory($imageItem->getImage(), $imageItem->getSubdirectory());
            $jsonResponse = json_encode(array('ok' => true));
        }

        return $this->getHttpJsonResponse($jsonResponse);
    }

    /**
     * @ParamConverter("imageItem", class="ImageBundle:ImageItem")
     */
    public function changeImageMainAction(ImageItem $imageItem, Request $request)
    {
        $jsonResponse = json_encode(array('ok' => false));
        if ($request->isXmlHttpRequest()) {
            $em = $this->getEntityManager();

            if (!$imageItem) {
                return $this->noPermission();
            }
            $imageMain = $imageItem->getItem()->getImageMain();
            if ($imageMain && $imageItem->getId() != $imageMain->getId()) {
                $imageMain->setMain(false);
                $imageItem->setMain(true);
                $em->persist($imageMain);
                $em->persist($imageItem);
                $em->flush();
                $jsonResponse = json_encode(array('ok' => true));
            }
            if (!$imageMain) {
                $imageItem->setMain(true);
                $em->persist($imageItem);
                $em->flush();
                $jsonResponse = json_encode(array('ok' => true));
            }
        }

        return $this->getHttpJsonResponse($jsonResponse);
    }
}
