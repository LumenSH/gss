<?php

namespace GSS\Component\Form\Forms;

use GSS\Component\Form\Type\Simditor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class BugCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            );

        $builder
            ->add(
                'typ',
                ChoiceType::class,
                [
                    'constraints' => [
                        new NotBlank(),
                    ],
                    'choices' => [
                        'Fehler' => 0,
                        'Feature' => 1,
                    ],
                ]
            );

        $builder
            ->add(
                'editor',
                Simditor::class,
                [
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            );
    }
}
