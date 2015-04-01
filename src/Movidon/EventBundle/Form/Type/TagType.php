<?php

namespace Movidon\EventBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', (array('required'=>true)));
    }

    public function getDefaultOptions(array $options)
    {
        return array('data_class' => 'Movidon\EventBundle\Entity\Tag');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'Movidon\EventBundle\Entity\Tag'));
    }

    public function getName()
    {
        return 'tag';
    }

}
