<?php

namespace Movidon\LocationBundle\Controller;

use Movidon\FrontendBundle\Controller\CustomController;
use Symfony\Component\HttpFoundation\Request;

class LocationController extends CustomController
{
    public function getCitiesAction(Request $request)
    {
        $em = $this->getEntityManager();
        $provinceId = $request->query->get('selected_parameter');
        $selectedCity = $request->query->get('selected_content');
        if (!is_numeric($provinceId)) {
            $province = $em->getRepository('LocationBundle:Province')->findOneBySlug($provinceId);
            $provinceId = $province->getId();
        }
        $cities = $em->getRepository('LocationBundle:City')->findBy(array('province' => $provinceId), array('name' => 'asc'));

        return $this->render('LocationBundle:Cities:listCities.html.twig', array('cities' => $cities, 'selectedCity' => $selectedCity));
    }

    public function getAddressesAction(Request $request)
    {
        $em = $this->getEntityManager();
        $user = $this->getCurrentUser();
        $addresses = $em->getRepository('LocationBundle:Address')->findAddressesByUser($user);

        return $this->render('UserBundle:Commons:addressList.html.twig', array('addresses' => $addresses, 'admin' => false));
    }
}
