<?php

namespace GSS\Component\Form\Forms;

use GSS\Component\Form\Type\ReCaptcha;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;

class PasswordResetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'email',
                EmailType::class,
                [
                    'constraints' => [
                        new Email(),
                    ],
                ]
            );

        $builder->add(
            'recaptcha',
            ReCaptcha::class
        );
    }
}
