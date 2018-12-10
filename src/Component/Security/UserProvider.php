<?php

namespace GSS\Component\Security;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class UserProvider
 */
class UserProvider implements UserProviderInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * UserProvider constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @throws UsernameNotFoundException if the user is not found
     *
     * @return UserInterface
     */
    public function loadUserByUsername($username)
    {
        $userData = $this->container->get('doctrine.dbal.default_connection')->fetchAssoc('SELECT * FROM users WHERE Username = ?', [
            $username,
        ]);

        if (empty($userData)) {
            throw new UsernameNotFoundException(\sprintf('User by name "%s" couldn\'t found', $username));
        }

        return new User($userData);
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     *
     * @throws UnsupportedUserException if the account is not supported
     * @throws \Exception
     *
     * @return UserInterface
     */
    public function refreshUser(UserInterface $user)
    {
        $userData = $this->container->get('doctrine.dbal.default_connection')->fetchAssoc('SELECT * FROM users WHERE Username = ?', [
            $user->getUsername(),
        ]);

        $session = $this->container->get('session');
        $session->set('userId', $userData['id']);

        if (!empty($userData['Permissions'])) {
            $userData['Permissions'] = \json_decode($userData['Permissions'], true);
        }

        if (!\is_array($userData['Permissions'])) {
            $userData['Permissions'] = [];
        }

        $session->set('user', $userData);

        $acl = new Acl(
            $this->container->get('doctrine.dbal.default_connection'),
            $this->container->getParameter('acl.build.roles'),
            $userData['Role'],
            $userData['Permissions']
        );

        $session->setAcl($acl);
        $session->onSessionStarted();

        return new User($userData);
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class instanceof User || $class === 'GSS\Component\Security\User';
    }
}
