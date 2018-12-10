<?php

namespace GSS\Component\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Simditor extends TextareaType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => [
                'data-ckeditor' => 'true',
                'novalidate' => 'novalidate',
            ],
        ]);
    }
}
