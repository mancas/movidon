<?php

namespace Movidon\ImageBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MultipleImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('images', 'file', array('attr' => array('multiple' => 'multiple')));
    }

    public function getName()
    {
        return 'multiple_images';
    }
}