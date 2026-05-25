<?php

namespace App\Form;

use App\Entity\ProjectStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectStatusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Название статуса',
                'attr' => ['placeholder' => 'Например: В работе'],
            ])
            ->add('color', ColorType::class, [
                'label' => 'Цвет',
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Порядок сортировки',
                'help' => 'Чем меньше число, тем выше статус в списке',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectStatus::class,
        ]);
    }
}