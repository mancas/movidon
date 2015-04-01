<?php
namespace Movidon\LocationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AddressDataBillingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('address', 'text', array('required' => true, 'attr' => array('placeholder' => 'Dirección')))
            ->add('postalCode', 'number', array('required' => true, 'attr' => array('placeholder' => 'Código Postal')));
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Movidon\LocationBundle\Entity\Address',
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Movidon\LocationBundle\Entity\Address'
        ));
    }

    public function getName()
    {
        return 'address';
    }
}