<?php

namespace Movidon\BackendBundle\Controller;

use Movidon\BackendBundle\Form\Type\CategoryBackendType;
use Movidon\EventBundle\Form\Type\TagType;
use Movidon\FrontendBundle\Controller\CustomController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Movidon\EventBundle\Entity\Tag;

class EventTagController extends CustomController
{
    public function indexAction()
    {
        $em = $this->getEntityManager();
        $tags = $em->getRepository("EventBundle:Tag")->findAll();

        return $this->render('BackendBundle:EventTag:index.html.twig', array('tags' => $tags));
    }

    public function createAction(Request $request)
    {
        $tag = new Tag();
        $form = $this->createForm(new TagType(), $tag);
        $formHandler = $this->get('tag.create_tag_form_handler');
        if ($formHandler->handle($form, $request)) {
            $this->setTranslatedFlashMessage("Tag creado");
            return $this->redirect($this->generateUrl('admin_event_tag_index'));
        }

        return $this->render('BackendBundle:EventTag:create.html.twig', array('form' => $form->createView()));
    }

    /**
     * @ParamConverter("$tag", class="EventBundle:Tag")
     */
    public function editAction(Tag $tag)
    {
        $form = $this->createForm(new TagType(), $tag);
        $formHandler = $this->get('tag.create_tag_form_handler');
        $request = $this->getRequest();
        if ($formHandler->handle($form, $request)) {
            $this->setTranslatedFlashMessage("Tag editado");
            return $this->redirect($this->generateUrl('admin_event_tag_index'));
        }

        return $this->render('BackendBundle:EventTag:create.html.twig',
            array('form' => $form->createView(),
                'tag' => $tag,
                'edition' => true));
    }

    /**
     * @ParamConverter("$tag", class="EventBundle:Tag")
     */
    public function deleteAction(Tag $tag)
    {
        $em = $this->getEntityManager();
        $em->remove($tag);
        $em->flush();
        $this->setTranslatedFlashMessage("Se ha eliminado el tag");

        return $this->redirect($this->generateUrl('admin_event_tag_index'));
    }
}
