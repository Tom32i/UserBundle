<?php

namespace Tom32i\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PasswordResetType extends AbstractType
{
    private $class;

    /**
     * @param string $class The User class name
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('plainPassword', 'repeated', array(
         'first_name' => 'password',
         'second_name' => 'confirm',
         'type' => 'password',
         'first_options' => array('label' => "Password"),
         'second_options' => array('label' => "Verification"),
         'label' => "Choose a new password",
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->class,
            'validation_groups' => array('reset')
        ));
    }

    public function getName()
    {
        return 'tom32i_user_password_reset';
    }
}
