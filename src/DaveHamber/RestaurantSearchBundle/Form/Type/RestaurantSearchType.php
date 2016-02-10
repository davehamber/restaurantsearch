<?php

namespace DaveHamber\RestaurantSearchBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Collection;

class RestaurantSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('address', 'text', array(
                'attr' => array(
                    'placeholder' => 'Please enter the address you would like to search.',
                    'pattern'     => '.{2,}' //minlength
                )
            ))
            ->add('Search', 'submit', array('label' => 'Search'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $collectionConstraint = new Collection(array(
            'address' => array(
                new NotBlank(array('message' => 'Please enter the address you would like to search.')),
                new Length(array('min' => 2))
            )
        ));

        $resolver->setDefaults(array(
            'constraints' => $collectionConstraint
        ));
    }

    public function getName()
    {
        return 'RestaurantSearch';
    }
}
