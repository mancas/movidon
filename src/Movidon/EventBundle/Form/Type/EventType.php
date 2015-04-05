<?php

namespace Movidon\EventBundle\Form\Type;

use Movidon\LocationBundle\Entity\City;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Movidon\ImageBundle\Form\Type\ImageType;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text');
        $builder->add('content', 'textarea', array('attr' => array('class' => 'tinymce', 'data-theme' => 'advanced')));
        $builder->add('tags', 'entity',
            array('class' => 'EventBundle:Tag',
                'required' => false,
                'multiple' => true,
                'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('t')->orderBy('t.name', 'ASC');
                    }, 'expanded' => false));
    }

    public function getDefaultOptions(array $options)
    {
        return array('data_class' => 'Movidon\EventBundle\Entity\Event');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'Movidon\EventBundle\Entity\Event'));
    }

    public function getName()
    {
        return 'event';
    }
}
