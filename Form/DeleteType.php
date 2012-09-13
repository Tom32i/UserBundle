<?php

namespace Tom32i\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Tom32i\UserBundle\Form\ImageType;

class DeleteType extends AbstractType
{
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
            'data_class' => 'Tom32i\UserBundle\Entity\User',
            'validation_groups' => array('delete')
        ));
    }

    public function getName()
    {
        return 'tom32i_userbundle_deletetype';
    }
}

?>
