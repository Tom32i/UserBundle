<?php

namespace Tom32i\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PasswordResetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('plainPassword', 'repeated', array(
         'first_name' => 'password',
         'second_name' => 'confirm',
         'type' => 'password',
         'first_options' => array('label' => "Password"),
         'second_options' => array('label' => "Verification"),
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Tom32i\UserBundle\Entity\User',
            'validation_groups' => array('reset')
        ));
    }

    public function getName()
    {
        return 'tom32i_userbundle_passwordresetTypetype';
    }
}
