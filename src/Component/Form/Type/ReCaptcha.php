<?php

namespace GSS\Component\Form\Type;

use GSS\Component\Validator\Constraints\ReCaptcha as ReCaptchaConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReCaptcha extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'constraints' => [
                new ReCaptchaConstraint(),
            ],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'recaptcha';
    }

    public function getParent()
    {
        return HiddenType::class;
    }
}
