<?php

namespace Movidon\EventBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Movidon\ImageBundle\Form\Type\ImageType;
use Movidon\LocationBundle\Form\Type\AddressType;

class SearchEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text', array('required'=>false));
        $builder->add('notPublished', 'checkbox', array('required'=>false));
        $builder->add('category', 'entity',
                array('class'=>'EventBundle:Tag',
                        'required' => false,
                        'multiple' => false,
                        'empty_value' => 'Selecciona un tag',
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('t')->orderBy('t.name', 'ASC');
                        }, 'expanded' => false));
        //TODO from-to when date
    }

    public function getName()
    {
        return 'search_event';
    }
}