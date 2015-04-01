<?php

namespace Movidon\LocationBundle\Command;

use Movidon\LocationBundle\Entity\City;
use Movidon\LocationBundle\Entity\Province;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportLocationCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('movidon:locations:import')->setDescription('It reads csv with locations');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $path = __DIR__ . '/../DataFixtures/provinces.csv';

        $handler = fopen($path, "r");

        while ($data = fgetcsv($handler, 1000, ',')) {
            $id = $data[0];
            if ($id) {
                $province = $em->getRepository('LocationBundle:Province')->findOneById($id);
                if (!$province) {
                    $province = new Province();
                    $province->setName($data[1]);
                    $province->setSlug($data[2]);
                    $em->persist($province);
                }
            }
        }
        $em->flush();

        $path = __DIR__ . '/../DataFixtures/cities.csv';

        $handler2 = fopen($path, "r");

        while ($data = fgetcsv($handler2, 1000, ',')) {
            $id = $data[0];
            if ($id) {
                $city = $em->getRepository('LocationBundle:City')->findOneById($id);
                if (!$city) {
                    $province = $em->getRepository('LocationBundle:Province')->findOneBySlug($data[3]);
                    if ($province) {
                        $city = new City();
                        $city->setId($id);
                        $city->setName($data[1]);
                        $city->setProvince($province);
                        $city->setSlug($data[2]);
                        $em->persist($city);
                    }
                }
            }
        }
        $em->flush();
    }
}
