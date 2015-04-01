<?php

namespace Movidon\BackendBundle\Form\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityManager;

class AdminUserFormHandler
{
    private $adminUserManager;

    public function __construct(AdminUserManager $adminUserManager)
    {
        $this->adminUserManager = $adminUserManager;
    }

    public function handle(FormInterface $form, Request $request)
    {
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $user = $form->getData();
                $this->adminUserManager->save($user);

                return true;
            }
        }

        return false;
    }

}