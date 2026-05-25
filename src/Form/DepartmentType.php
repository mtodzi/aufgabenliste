<?php

namespace App\Form;

use App\Entity\Department;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepartmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Название подразделения',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание',
                'required' => false,
            ])
            ->add('employeeRoles', EntityType::class, [
                'class' => Role::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false,
                'label' => 'Связанные роли',
                'query_builder' => function (EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('r');
                    if ($options['current_organization']) {
                        $qb->where('r.organization = :org')
                           ->setParameter('org', $options['current_organization']);
                    }
                    return $qb->orderBy('r.name', 'ASC');
                },
                'attr' => [
                    'class' => 'form-select select2',
                    'data-placeholder' => 'Выберите роли...',
                ],
            ])
            ->add('users', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getFullName() ?? $user->getEmail();
                },
                'query_builder' => function (EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('u');
                    if ($options['current_organization']) {
                        $qb->where('u.organization = :org')
                           ->setParameter('org', $options['current_organization']);
                    }
                    return $qb->orderBy('u.fullName', 'ASC')
                              ->addOrderBy('u.email', 'ASC');
                },
                'label' => 'Сотрудники',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'by_reference' => false,
                'attr' => [
                    'class' => 'form-select select2',
                    'data-placeholder' => 'Выберите сотрудников...',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Department::class,
            'current_organization' => null,
        ]);
    }
}