<?php

namespace GSS\Component\Form\Forms;

use GSS\Component\Form\Type\ReCaptcha;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SmsVerificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'mobilenumber',
                TextType::class
            );

        $builder->add(
            'recaptcha',
            ReCaptcha::class
        );
    }
}
