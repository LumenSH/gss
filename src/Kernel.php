<?php

namespace GSS;

use GSS\Component\DependencyInjection\Compiler\AclCompilerPass;
use GSS\Component\DependencyInjection\Compiler\CustomTwigPass;
use GSS\Component\DependencyInjection\Compiler\SentryListenerPass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    /**
     * @return string
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function getCacheDir()
    {
        return $this->getProjectDir() . '/var/cache/' . $this->environment;
    }

    /**
     * @return string
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function getLogDir()
    {
        return $this->getProjectDir() . '/var/log';
    }

    /**
     * @return \Generator|\Symfony\Component\HttpKernel\Bundle\BundleInterface[]
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function registerBundles()
    {
        $contents = require $this->getProjectDir() . '/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                yield new $class();
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param LoaderInterface  $loader
     *
     * @throws \Exception
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $container->setParameter('container.autowiring.strict_mode', true);
        $container->setParameter('container.dumper.inline_class_loader', true);
        $confDir = $this->getProjectDir() . '/config';

        $loader->load($confDir . '/{packages}/*' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{packages}/' . $this->environment . '/**/*' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{services}' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{services}_' . $this->environment . self::CONFIG_EXTS, 'glob');

        $container->addCompilerPass(new AclCompilerPass());
        $container->addCompilerPass(new CustomTwigPass());

        $rev = 'dev';
        if (\file_exists($container->getParameter('kernel.project_dir') . '/.rev')) {
            $rev = \file_get_contents($container->getParameter('kernel.project_dir') . '/.rev');
        }

        $container->setParameter('revision', $rev);
        $container->setParameter('kernel.public_dir', $container->getParameter('kernel.project_dir') . '/public/');
    }

    /**
     * @param RouteCollectionBuilder $routes
     *
     * @throws \Symfony\Component\Config\Exception\FileLoaderLoadException
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $confDir = $this->getProjectDir() . '/config';

        $routes->import($confDir . '/{routes}/*' . self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir . '/{routes}/' . $this->environment . '/**/*' . self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir . '/{routes}' . self::CONFIG_EXTS, '/', 'glob');
    }
}
