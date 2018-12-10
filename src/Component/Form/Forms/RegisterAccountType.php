<?php

namespace GSS\Component\Form\Forms;

use GSS\Component\Form\Type\ReCaptcha;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegisterAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'email',
                EmailType::class,
                [
                    'constraints' => [
                        new Email(),
                        new NotBlank(),
                    ],
                    'attr' => [
                        'data-mailgun' => 'true',
                    ],
                ]
            );

        $builder
            ->add(
                'username',
                TextType::class,
                [
                    'constraints' => [
                        new NotBlank(),
                    ],
                    'label' => __('Username', 'User', 'Username'),
                ]
            );

        $builder->add('password', RepeatedType::class, [
            'type' => PasswordType::class,
            'invalid_message' => __('Die Passwörter müssen übereinstimmen', 'User', 'PasswordEqual'),
            'required' => true,
            'first_options' => ['label' => __('Neues Passwort', 'User', 'NewPassword')],
            'second_options' => ['label' => __('Neues Passwort wiederholen', 'User', 'NewPassword2')],
        ]);

        $builder->add('privacy', CheckboxType::class, [
            'label' => __('Die Datenschutzbestimmungen habe ich zur Kenntnis genommen', 'User', 'Privacy'),
            'required' => true
        ]);

        $builder->add(
            'recaptcha',
            ReCaptcha::class
        );
    }
}
