<?php

namespace GSS\Component\Form\Forms;

use GSS\Component\Form\Type\ReCaptcha;
use GSS\Component\Form\Type\Simditor;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class SupportTicketType extends AbstractType
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

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

        $tmpConfig = $this->container->getParameter('support');
        $gameserver = $this->container->get('app.user.user')->getGameserver($this->container->get('session')->getUserID());

        $gsConfig = [];

        foreach ($gameserver as $value) {
            $gsConfig[$value['string']] = $value['id'];
        }

        $config = [];

        foreach ($tmpConfig as $key => $item) {
            $config[$item] = $key;
        }

        $builder
            ->add(
                'typ',
                ChoiceType::class,
                [
                    'constraints' => [
                        new NotBlank(),
                    ],
                    'choices' => $config,
                ]
            );

        $builder
            ->add(
                'gameserver',
                ChoiceType::class,
                [
                    'choices' => $gsConfig,
                ]
            );

        $builder
            ->add(
                'question',
                Simditor::class,
                [
                    'constraints' => [
                        new NotBlank(),
                    ],
                    'label' => __('Frage', 'Support', 'Question'),
                ]
            );

        $builder->add(
            'recaptcha',
            ReCaptcha::class
        );
    }
}
