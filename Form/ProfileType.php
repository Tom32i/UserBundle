<?php

namespace Tom32i\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProfileType extends AbstractType
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

        $builder
            ->add('username')
            ->add('email')
            ->add('currentPassword', 'password', array(
                'label' => "Current password",
                'required' => false,
            ))
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
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->class,
            'validation_groups' => array('profile')
        ));
    }

    public function getName()
    {
        return 'tom32i_user_profile';
    }
}

?>
