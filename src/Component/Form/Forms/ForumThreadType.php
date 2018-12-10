<?php

namespace GSS\Component\Form\Forms;

use GSS\Component\Form\Type\Simditor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ForumThreadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, [
            'constraints' => [
                new NotBlank(),
                new Length([
                    'min' => 5,
                    'max' => 30,
                ]),
            ],
            'label' => __('Name:', 'Forum', 'ThreadName'),
        ]);

        $builder->add('editor', Simditor::class, [
            'constraints' => [
                new Length([
                    'min' => 10,
                    'max' => 1000,
                ]),
            ],
            'label' => __('Nachricht:', 'Forum', 'ThreadMessage'),
        ]);
    }
}
