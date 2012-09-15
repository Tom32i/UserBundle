<?php

namespace Tom32i\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    { 

        $builder
            ->add('username')
            ->add('email')
            ->add('plainPassword', 'repeated', array(
                'first_name' => 'password',
                'second_name' => 'confirm',
                'label' => "New password",
                'type' => 'password',
                'required' => false,
                'first_options' => array('label' => "Password"),
                'second_options' => array('label' => "Verification"),
            ))
        ;

        //$builder->get('current_password')->setAttribute('label', 'current password');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Tom32i\UserBundle\Entity\User',
            'validation_groups' => array('profile')
        ));
    }

    public function getName()
    {
        return 'tom32i_userbundle_profiletype';
    }
}

?>
