<?php

namespace GSS\Component\Form\Forms;

use GSS\Component\Form\Type\Simditor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;

class ForumPostEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
