<?php

namespace GSS\Component\Twig;

use Fig\Link\GenericLinkProvider;
use Fig\Link\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Extension;
use Twig_SimpleFunction;

class ExtensionFunctions extends Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private $hasAddedLinks = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('getNotfications', [$this, 'getNotfications'], [
                'is_safe' => ['html'],
            ]),
            new \Twig_SimpleFunction('__', [$this, 'translate'], [
                'is_safe' => ['html'],
            ]),
            new \Twig_SimpleFunction('avatar', [$this, 'getAvatar'], [
                'is_safe' => ['html'],
            ]),
            new \Twig_SimpleFunction('is_external', [$this, 'isExternal']),
            new \Twig_SimpleFunction('hasPermission', [$this, 'hasPermission']),
        ];
    }

    /**
     * Returns the Notification.
     *
     * @return string
     */
    public function getNotfications()
    {
        $allData = $this->container->get('session')->getFlashBag()->all();

        $js = '';

        foreach ($allData as $type => $messages) {
            foreach ($messages as $message) {
                list($title, $message) = \explode('|', $message);
                $js .= \sprintf("gsAlert('%s', '%s', '%s');\n", $type, $this->escapeJavaScriptText($title), $this->escapeJavaScriptText($message));
            }
        }

        $this->container->get('session')->getFlashBag()->clear();

        return $js;
    }

    /**
     * Return the translation.
     *
     * @param string $defaultValue
     * @param string $name
     * @param string $namespace
     * @param array  $replace
     *
     * @return mixed|string
     */
    public function translate($defaultValue = '', $name = '', $namespace = '', $replace = [])
    {
        $output = $this->container->get('translation')->getString($name, $defaultValue, $namespace);

        foreach ($replace as $key => $param) {
            $output = \str_replace('%' . $key . '%', $param, $output);
        }

        return $output;
    }

    /**
     * Returns the avatar.
     *
     * @param $id
     * @param string $class
     *
     * @return string
     */
    public function getAvatar($id, $class = '')
    {
        if (empty($id)) {
            $id = 'src/img/noimage@2x.png';
        } else {
            $id = 'uploads/avatar/' . $id;
        }

        $imageUrl = $this->container->getParameter('url') . $id;
        $preloadImage = '/' . $id;

        // Add avatar to preload
        if ($request = $this->container->get('request_stack')->getMasterRequest()) {
            if (!\in_array($preloadImage, $this->hasAddedLinks)) {
                $link = new Link('preload', $preloadImage);
                $link = $link->withAttribute('as', 'image');
                $linkProvider = $request->attributes->get('_links', new GenericLinkProvider());
                $request->attributes->set('_links', $linkProvider->withLink($link));
                $this->hasAddedLinks[] = $preloadImage;
            }
        }

        return '<img src="' . $imageUrl . '" class="' . $class . '"/>';
    }

    public function hasPermission($permission = null)
    {
        return $this->container->get('session')->Acl()->isAllowed($permission);
    }

    public function isExternal($url)
    {
        return \strstr($url, 'http:') || \strstr($url, 'https:');
    }

    private function escapeJavaScriptText($string)
    {
        return \str_replace("\n", '\n', \str_replace('"', '\"', \addcslashes(\str_replace("\r", '', (string) $string), "\0..\37'\\")));
    }
}
