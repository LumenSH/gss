<?php

namespace GSS\Component\HttpKernel;

use GSS\Component\Twig\Twig;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Default Controller with preimplemented functions
 * Class Controller.
 */
abstract class Controller extends BaseController
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var int
     */
    protected $userID = null;

    /**
     * @var array
     */
    protected $data = [];

    public function init()
    {
    }

    /**
     * @return Request
     */
    public function Request()
    {
        return $this->container->get('request');
    }

    /**
     * @return Twig
     */
    public function View()
    {
        return $this->container->get('twig');
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->userID = $this->getUser() ? $this->getUser()->getId() : null;
        $this->init();
    }

    /**
     * Reloads the page.
     */
    public function reload()
    {
        return new RedirectResponse($_SERVER['REQUEST_URI']);
    }
}
