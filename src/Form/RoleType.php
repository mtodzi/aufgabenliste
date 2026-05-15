<?php

namespace App\Form;

use App\Entity\Permission;
use App\Entity\Role;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Название роли (техническое)',
                'attr' => ['placeholder' => 'ROLE_ORG_...']
            ])
            ->add('title', TextType::class, [
                'label' => 'Название роли (понятное пользователю)',
                'required' => false,
                'attr' => ['placeholder' => 'Например: Менеджер, Бухгалтер...']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание роли',
                'required' => false,
                'attr' => ['placeholder' => 'Краткое описание прав и обязанностей...', 'rows' => 3]
            ])
            ->add('permissions', EntityType::class, [
                'class' => Permission::class,
                'choice_label' => 'description',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Права доступа'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Role::class,
        ]);
    }
}