<?php

namespace App\Form;

use App\Entity\Route;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RouteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startLocation', TextType::class)
            ->add('endLocation', TextType::class)
            ->add('waypoints', TextType::class)
            ->add('estimationDuration', IntegerType::class)
            ->add('vehiculeType', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Route::class,
        ]);
    }
}
