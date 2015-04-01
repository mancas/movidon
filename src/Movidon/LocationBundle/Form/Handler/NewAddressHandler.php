<?php

namespace Movidon\LocationBundle\Form\Handler;

use Doctrine\ORM\EntityManager;
use Movidon\LocationBundle\Entity\Address;
use Movidon\PaymentBundle\Entity\DataBilling;
use Movidon\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;

class NewAddressHandler
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function handle(FormInterface $form, Request $request, User $user)
    {
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $address = $form->getData();
                $address->setUser($user);
                $user->addAddress($address);
                $city = $request->request->get('c');
                if (isset($city)) {
                    $city = $this->em->getRepository('LocationBundle:City')->findOneById($city);
                    $address->setCity($city);
                }

                $dataBilling = new DataBilling();
                $dataBilling->setAddress($address);
                $address->setDataBilling($dataBilling);
                $dataBilling->setName($user->getName());
                $dataBilling->setCorporateName($user->getLastName());
                $dataBilling->setEmail($user->getEmail());
                $dataBilling->setPhone($user->getPhone());

                $this->em->persist($address);
                $this->em->persist($dataBilling);
                $this->em->persist($user);
                $this->em->flush();

                return true;
            }
        }

        return false;
    }
}