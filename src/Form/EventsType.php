<?php

namespace App\Form;

use App\Entity\Events;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class EventsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('eventName')
            ->add('eventDescription')
            ->add('startDate', null, [
                'widget' => 'single_text',
            ])
            ->add('endDate', null, [
                'widget' => 'single_text',
            ])
            ->add('location', TextType::class, [
                'label' => 'Location',
                'attr' => [
                    'readonly' => true,
                    'class' => 'form-control location-input',
                    'placeholder' => 'Select a location on the map'
                ]
            ])
            ->add('latitude', HiddenType::class, [
                'required' => false,
            ])
            ->add('longitude', HiddenType::class, [
                'required' => false,
            ])         
            ->add('category')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Events::class,
        ]);
    }
}
