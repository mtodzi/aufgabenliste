<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmployeeRoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('userRoles', EntityType::class, [
                'class' => Role::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Доступные роли организации',
                'by_reference' => false,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('r');
                    
                    if (!$options['is_system_admin']) {
                        // Показываем роли этой организации И системные роли для организаций
                        $qb->where('r.organization = :org')
                           ->orWhere('r.organization IS NULL AND r.name LIKE :prefix')
                           ->setParameter('org', $options['current_organization'])
                           ->setParameter('prefix', 'ROLE_ORG_%');
                    }
                    
                    return $qb->orderBy('r.name', 'ASC');
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_system_admin' => false,
            'current_organization' => null,
        ]);
    }
}