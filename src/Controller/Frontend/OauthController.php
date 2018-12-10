<?php

namespace GSS\Controller\Frontend;

use Depotwarehouse\OAuth2\Client\Twitch\Entity\TwitchUser;
use Depotwarehouse\OAuth2\Client\Twitch\Provider\Twitch;
use Discord\OAuth2\Client\Provider\Discord;
use GSS\Component\HttpKernel\Controller;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Facebook;
use League\OAuth2\Client\Provider\FacebookUser;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class OauthController
 */
class OauthController extends Controller
{
    /**
     * @var array
     */
    const ALLOWED_SERVICES = [
        'facebook',
        'google',
        'twitch',
        'discord',
    ];

    /**
     * @Route("/oauth")
     * @Route("/oauth/")
     *
     * @return RedirectResponse
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function indexAction()
    {
        if ($this->Request()->query->has('service')) {
            $this->container->get('session')->set('oauthService', $this->Request()->query->get('service'));
        }

        $service = $this->container->get('session')->get('oauthService', 'facebook');

        if (!\in_array($service, self::ALLOWED_SERVICES)) {
            $service = 'facebook';
        }

        /** @var GenericProvider $fb */
        $provider = $this->getProvider($service);

        if ($this->Request()->query->has('code')) {
            try {
                $token = $provider->getAccessToken('authorization_code', [
                    'code' => $_GET['code'],
                ]);
            } catch (\Exception $e) {
                return new RedirectResponse('/oauth?service=' . $service);
            }

            try {
                /** @var FacebookUser $user */
                $user = $provider->getResourceOwner($token);
                $userDetails = $this->extractUserInformations($user);
            } catch (\Exception $e) {
                return $this->redirectToRoute('index');
            }

            $userDetails['email'] = \str_replace('@googlemail.com', '@gmail.com', $userDetails['email']);

            if ($this->container->get('app.user.user')->isRegistered($userDetails['email'])) {
                $this->container->get('app.user.user')->loginWithEmail($this->Request(), $userDetails['email']);

                return $this->redirectToRoute('index');
            }

            $this->container->get('session')->set('oauthMail', $userDetails['email']);

            return $this->redirectToRoute('gss_frontend_oauth_completeoauthregister');
        }

        $this->container->get('session')->set('oauthService', $service);

        return new RedirectResponse((string) $provider->getAuthorizationUrl());
    }

    /**
     * @Route("/oauth/completeOAuthRegister")
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax*
     *
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function completeOAuthRegisterAction()
    {
        $this->View()->setPageTitle('Registrierung abschliessen');

        if (!$this->container->get('session')->has('oauthMail')) {
            return $this->redirectToRoute('index');
        }

        return $this->render('frontend/user/completeRegister.twig', $this->data);
    }

    /**
     * @Route("/oauth/last")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function lastAction()
    {
        $mail = $this->container->get('session')->get('oauthMail');
        $username = $this->Request()->getPost('name');

        if (\strlen($username) < 5) {
            return new JsonResponse(['success' => false, 'message' => __('Dein Username ist zu kurz', 'OAuth', 'UsernameToShort')]);
        }

        if ($this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT COUNT(*) FROM users WHERE Email LIKE ?', [$mail]) > 0) {
            return new JsonResponse(['success' => false, 'message' => 'toIndex']);
        }

        if (empty($mail)) {
            return new JsonResponse(['success' => false, 'message' => 'toIndex']);
        }

        if ($this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT COUNT(*) FROM users WHERE Username LIKE ?', [$username]) == 0) {
            $this->container->get('app.user.user')->registerUser([
                'username' => $username,
                'email' => $mail,
                'password' => \uniqid('gss', true),
                'fb' => true,
            ]);

            $this->container->get('app.user.user')->loginWithEmail($this->Request(), $mail);

            return new JsonResponse([
                'success' => true,
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'message' => __('Der Username ist bereits in Benutzung', 'User', 'UsernameIsAlreadyTaken'),
        ]);
    }

    /**
     * @param $user
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private function extractUserInformations($user): array
    {
        switch (\get_class($user)) {
            case FacebookUser::class:
            case GoogleUser::class:
            case TwitchUser::class:
                return [
                    'email' => $user->getEmail(),
                ];

            case \Discord\OAuth2\Client\Provider\Entity\User::class:
                return [
                    'email' => $user->getEmail()->__toString(),
                ];
        }
    }

    /**
     * @param string $service
     *
     * @return AbstractProvider
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    private function getProvider(string $service)
    {
        switch ($service) {
            case 'google':
                return $this->container->get(Google::class);
            case 'facebook':
                return $this->container->get(Facebook::class);
            case 'twitch':
                return $this->container->get(Twitch::class);
            case 'discord':
                return $this->container->get(Discord::class);
        }
    }
}
