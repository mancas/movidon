<?php

namespace Movidon\FrontendBundle\Form\Handler;

use Movidon\FrontendBundle\Form\Handler\TagManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityManager;

class CreateTagFormHandler
{
    private $tagManager;

    public function __construct(TagManager $tagManager)
    {
        $this->tagManager = $tagManager;
    }

    public function handle(FormInterface $form, Request $request)
    {
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $tag = $form->getData();
                $this->tagManager->save($tag);

                return true;
            }
        }

        return false;
    }

}
