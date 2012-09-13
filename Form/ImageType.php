<?php

namespace Tom32i\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file')
                ->add('remove', 'hidden', array('data' => '0'));
    }

    public function getName()
    {
        return 'tom32i_userbundle_imagetype';
    }
        
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Tom32i\SiteBundle\Entity\Picture',
        );
    }
}
