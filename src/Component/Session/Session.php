<?php

namespace GSS\Component\Session;

use Doctrine\DBAL\Connection;
use GSS\Component\Commerce\GP;
use GSS\Component\Security\Acl;
use SessionHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class Session extends SymfonySession
{
    /**
     * @var NativeSessionStorage
     */
    private $nativeSessionStorage;

    /**
     * @var Acl
     */
    private $acl;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Session constructor.
     *
     * @param SessionHandlerInterface $sessionHandler
     * @param ContainerInterface      $container
     */
    public function __construct(
        SessionHandlerInterface $sessionHandler,
        ContainerInterface $container
    ) {
        $this->container = $container;

        if (PHP_SAPI !== 'cli') {
            $this->nativeSessionStorage = new NativeSessionStorage([
                'name' => 'gss_' . $this->container->get('kernel')->getEnvironment(),
            ], $sessionHandler);

            parent::__construct($this->nativeSessionStorage, new NamespacedAttributeBag(), new FlashBag());
        } else {
            parent::__construct(new MockArraySessionStorage(), new NamespacedAttributeBag(), new FlashBag());
        }

        $this->acl = new Acl(
            $this->container->get('doctrine.dbal.default_connection'),
            $this->container->getParameter('acl.build.roles'),
            'default',
            []
        );
    }

    /**
     * @return FlashMessenger
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function flashMessenger()
    {
        return $this->container->get('flash.messenger');
    }

    /**
     * @param bool $default
     *
     * @return mixed
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getUserID($default = false)
    {
        return $this->get('userId', $default);
    }

    /**
     * @param string $item
     * @param null   $default
     *
     * @return mixed|null
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getUserData($item = '', $default = null)
    {
        $user = $this->get('user', false);

        if (empty($item)) {
            return $user;
        }

        if ($user) {
            return $user[$item] ?? $default;
        }

        return $default;
    }

    /**
     * @return Acl
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function Acl()
    {
        return $this->acl;
    }

    /**
     * @param Acl $acl
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function setAcl(Acl $acl)
    {
        $this->acl = $acl;
    }

    /**
     * @throws \Exception
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function onSessionStarted()
    {
        if (!empty($this->get('user/Inhibition'))) {
            $this->invalidate();
            throw new AccessDeniedException();
        }

        if (empty($this->has('userSlug'))) {
            $this->set('userSlug', $this->container->get('rewrite_manager')->getRewriteByParams([
                'userID' => $this->getUserID(),
            ])['link']);
        }
        if (\date('d', $this->get('user/LastLogin')) !== \date('d')) {
            $this->container->get(GP::class)->addPointsToUser(
                $this->getUserID(),
                $this->container->getParameter('gppoints.dailylogin'),
                'Daily Login'
            );
            $this->container->get('doctrine.dbal.default_connection')->executeQuery('UPDATE users SET LastLogin = ? WHERE id = ?', [\time(), $this->getUserID()]);
        }
    }
}
