<?php
namespace Movidon\BackendBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AdminUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', 'text', array('error_bubbling' => 'true'))
            ->add('password', 'repeated', array(
                'type' => 'password',
                'first_options' => array('attr' => array('placeholder' => 'Password')),
                'second_options' => array('attr' => array('placeholder' => 'Confirmar password')),
                'error_bubbling' => 'true'));
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Movidon\BackendBundle\Entity\AdminUser',
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Movidon\BackendBundle\Entity\AdminUser'
        ));
    }

    public function getName()
    {
        return 'admin_user';
    }
}