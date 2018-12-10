<?php

namespace GSS\Component\Security;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;

class LoginAuthenticator extends AbstractFormLoginAuthenticator
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function supports(Request $request)
    {
        return $request->request->has('_username');
    }

    /**
     * @param Request $request
     *
     * @return array|mixed|null
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function getCredentials(Request $request)
    {
        if ($request->request->has('username')) {
            $request->request->set('_username', $request->request->get('username'));
            $request->request->set('_password', $request->request->get('password'));
        }

        if ($request->request->has('_username')) {
            $username = $request->request->get('_username');
            $password = $request->request->get('_password');

            return [
                'username' => $username,
                'password' => $password,
            ];
        }

        return null;
    }

    /**
     * Return a UserInterface object based on the credentials.
     *
     * The *credentials* are the return value from getCredentials()
     *
     * You may throw an AuthenticationException if you wish. If you return
     * null, then a UsernameNotFoundException is thrown for you.
     *
     * @param mixed                 $credentials
     * @param UserProviderInterface $userProvider
     *
     * @throws AuthenticationException
     *
     * @return UserInterface|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $username = $credentials['username'];

        $user = $this->container->get('doctrine.dbal.default_connection')->fetchAssoc('SELECT * FROM users WHERE Username = :name OR Email = :name', [
            'name' => $username,
        ]);

        if (!\is_array($user)) {
            $user = [
                'Password' => '',
                'Salt' => '',
                'Email' => '',
            ];
        }

        return new User($user);
    }

    /**
     * Returns true if the credentials are valid.
     *
     * If any value other than true is returned, authentication will
     * fail. You may also throw an AuthenticationException if you wish
     * to cause authentication to fail.
     *
     * The *credentials* are the return value from getCredentials()
     *
     * @param mixed         $credentials
     * @param UserInterface $user
     *
     * @throws AuthenticationException
     *
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        if ($this->container->get('app.security.password_encoder.bcrypt')->verify($user->getPassword(), $credentials['password'], $user->getSalt())) {
            return true;
        }

        throw new AuthenticationException(__('Deine Email-Adresse oder dein Passwort ist falsch', 'User', 'LoginError'));
    }

    /**
     * @param Request        $request
     * @param TokenInterface $token
     * @param string         $providerKey
     *
     * @return JsonResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return new JsonResponse(['success' => true]);
    }

    /**
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return JsonResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse(['success' => false, 'message' => $exception->getMessage(), 'type' => \get_class($exception)]);
    }

    /**
     * Return the URL to the login page.
     *
     * @return string
     */
    protected function getLoginUrl()
    {
        return $this->container->get('router')->generate('index');
    }
}
