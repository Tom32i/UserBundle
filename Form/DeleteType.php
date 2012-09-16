<?php

namespace Tom32i\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Tom32i\UserBundle\Form\ImageType;

class DeleteType extends AbstractType
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
        $builder->add('currentPassword', 'password', array(
            'label' => "Current password",
            'required' => true,
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->class,
            'validation_groups' => array('delete')
        ));
    }

    public function getName()
    {
        return 'tom32i_user_delete';
    }
}

?>
