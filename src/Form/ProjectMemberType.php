<?php

namespace App\Form;

use App\Entity\ProjectMember;
use App\Entity\ProjectRole;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectMemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getFullName() ?? $user->getEmail();
                },
                'query_builder' => function (EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('u');
                    if (!empty($options['current_organization'])) {
                        $qb->where('u.organization = :org')
                           ->setParameter('org', $options['current_organization']);
                    }
                    return $qb->orderBy('u.fullName', 'ASC')
                              ->addOrderBy('u.email', 'ASC');
                },
                'label' => 'Сотрудник',
                'attr' => [
                    'class' => 'form-select select2',
                    'data-placeholder' => 'Выберите сотрудника...',
                ],
            ])
            ->add('role', EntityType::class, [
                'class' => ProjectRole::class,
                'choice_label' => 'name',
                'label' => 'Роль',
                'query_builder' => function (EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('pr');
                    if (!empty($options['current_organization'])) {
                        $qb->where('pr.organization = :org OR pr.organization IS NULL')
                           ->setParameter('org', $options['current_organization']);
                    }
                    return $qb->orderBy('pr.name', 'ASC');
                },
                'attr' => ['class' => 'form-select'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectMember::class,
            'current_organization' => null,
        ]);
    }
}