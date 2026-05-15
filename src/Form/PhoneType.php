<?php

namespace App\Form;

use App\Entity\Phone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class PhoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', TextType::class, [
                'label' => 'Номер телефона',
                'attr' => [
                    'placeholder' => '+7 (999) 999-99-99',
                    'class' => 'phone-mask'
                ],
                'constraints' => [
                    new NotBlank(message: 'Пожалуйста, введите номер телефона.'),
                    new Regex(
                        pattern: '/^\+?[\d\s\-\(\)]{7,20}$/',
                        message: 'Пожалуйста, введите корректный номер телефона.'
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Phone::class,
        ]);
    }
}