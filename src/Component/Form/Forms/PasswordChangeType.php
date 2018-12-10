<?php

namespace GSS\Component\Form\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;

class PasswordChangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('password', RepeatedType::class, [
            'type' => PasswordType::class,
            'invalid_message' => __('Die Passwörter müssen übereinstimmen', 'User', 'PasswordEqual'),
            'required' => true,
            'first_options' => ['label' => __('Neues Passwort', 'User', 'NewPassword')],
            'second_options' => ['label' => __('Neues Passwort wiederholen', 'User', 'NewPassword2')],
        ]);
    }
}
