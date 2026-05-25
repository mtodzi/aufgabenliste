<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\ProjectStatus;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Название проекта'])
            ->add('description', TextareaType::class, [
                'label' => 'Описание',
                'required' => false,
            ])
            ->add('status', EntityType::class, [
                'class' => ProjectStatus::class,
                'choice_label' => 'name',
                'label' => 'Статус проекта',
                'required' => false,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('ps');
                    if (!empty($options['current_organization'])) {
                        $qb->where('ps.organization = :org')
                           ->setParameter('org', $options['current_organization']);
                    }
                    return $qb->orderBy('ps.sortOrder', 'ASC');
                },
                'attr' => ['class' => 'form-select'],
            ])
            ->add('plannedStartDate', DateType::class, [
                'label' => 'Плановая дата начала',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('plannedEndDate', DateType::class, [
                'label' => 'Плановая дата завершения',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('members', CollectionType::class, [
                'entry_type' => ProjectMemberType::class,
                'entry_options' => ['current_organization' => $options['current_organization']],
                'label' => 'Участники проекта',
                'allow_add' => true,      // Разрешает динамическое добавление
                'allow_delete' => true,   // Разрешает динамическое удаление
                'by_reference' => false,  // Заставит Symfony вызывать $project->addMember()
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
            'current_organization' => null,
        ]);
    }
}