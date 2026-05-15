<?php

namespace App\Form;

use App\Entity\User;
use App\Form\PhoneType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class EmployeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['placeholder' => 'example@mail.com'],
                'constraints' => [
                    new NotBlank(message: 'Пожалуйста, введите email'),
                ],
            ])
            ->add('fullName', TextType::class, [
                'label' => 'ФИО',
                'attr' => ['placeholder' => 'Иванов Иван Иванович'],
                'constraints' => [
                    new NotBlank(message: 'Пожалуйста, введите ФИО'),
                ],
            ])
            ->add('phones', CollectionType::class, [
                'entry_type' => PhoneType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false, // Важно для OneToMany связей
                'label' => 'Телефоны',
                'required' => false,
            ])
        ;

        if (!$options['is_edit']) {
            $builder
                ->add('generatePassword', CheckboxType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Сгенерировать пароль автоматически',
                    'attr' => ['class' => 'form-check-input'],
                    'row_attr' => ['class' => 'form-check form-switch mt-3'],
                ])
                ->add('plainPassword', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'first_options'  => ['label' => 'Пароль'],
                    'second_options' => [
                        'label' => 'Повторите пароль',
                        'attr' => ['placeholder' => '••••••••'],
                    ],
                    'invalid_message' => 'Пароли должны совпадать.',
                    'mapped' => false,
                    'attr' => ['autocomplete' => 'new-password'],
                    'required' => false,
                ]);

            $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();

                // Если чекбокс "Сгенерировать пароль" не отмечен, добавляем валидацию
                if (empty($data['generatePassword'])) {
                    $form->add('plainPassword', RepeatedType::class, [
                        'type' => PasswordType::class,
                        'first_options'  => ['label' => 'Пароль'],
                        'second_options' => ['label' => 'Повторите пароль', 'attr' => ['placeholder' => '••••••••']],
                        'invalid_message' => 'Пароли должны совпадать.',
                        'mapped' => false,
                        'attr' => ['autocomplete' => 'new-password'],
                        'constraints' => [
                            new NotBlank(message: 'Пожалуйста, введите пароль'),
                            new Length(
                                min: 6,
                                minMessage: 'Пароль должен быть не менее {{ limit }} символов',
                                max: 4096,
                            ),
                        ],
                    ]);
                }
            });
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
        ]);
    }
}
