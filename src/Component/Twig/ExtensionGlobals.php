<?php

namespace GSS\Component\Twig;

use GSS\Component\Security\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig_Extension;
use Twig_SimpleFunction;

/**
 * Class ExtensionGlobals
 *
 * @author Soner Sayakci <***REMOVED***>
 */
class ExtensionGlobals extends Twig_Extension
{
    /**
     * @var array
     */
    private $jsData = [];
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var User
     */
    private $user;

    /**
     * ExtensionGlobals constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $twig = $container->get('twig');

        if ($container->get('security.token_storage')->getToken()) {
            if ($container->get('security.token_storage')->getToken()->getUser() instanceof UserInterface) {
                $this->user = $container->get('security.token_storage')->getToken()->getUser();
            }
        }

        $twig->addGlobal('baseUrl', $this->container->getParameter('url'));
        $twig->addGlobal('isLoggedIn', $this->user !== null);
        $twig->addGlobal('rev', $this->container->getParameter('revision'));
        $twig->addGlobal('version', $this->container->getParameter('version'));
        $twig->addGlobal('languages', $this->container->getParameter('language')['mapping']);

        $indicents = $this->container->get('cache')->get('incidents');

        if (!empty($indicents)) {
            $twig->addGlobal('incidents', $indicents);
        } else {
            $twig->addGlobal('incidents', []);
        }

        if ($this->user) {
            $user = $this->user->getData();
            $this->filterUser($user);
            $twig->addGlobal('User', $user);
        }

        $module = 'frontend';
        $controller = 'index';
        $action = 'index';

        if ($this->container->get('request_stack')->getCurrentRequest() === null) {
            return;
        }

        try {
            $routeExplode = \explode('_', $this->container->get('request_stack')->getCurrentRequest()->attributes->get('_route'));

            if (!empty($routeExplode[1])) {
                $module = $routeExplode[1];
            }

            if (!empty($routeExplode[2])) {
                $controller = $routeExplode[2];
            }

            if (!empty($routeExplode[3])) {
                $action = $routeExplode[3];
            }

            if ($this->container->get('request_stack')->getCurrentRequest()->attributes->get('_route') === 'cms') {
                $controller = 'cms';
            }
        } catch (\Exception $e) {
        }

        $twig->addGlobal('request', [
            'controller' => \ucfirst($controller),
            'action' => \ucfirst($action),
            'module' => \ucfirst($module),
            'language' => $this->container->get('language')->getLanguage(),
            'url' => 'https://' . $this->container->get('request')->getHttpHost() . $this->container->get('request')->getRequestUri(),
            'cookies' => $this->container->get('request')->cookies->all(),
        ]);

        if (!$this->container->get('request_stack')->getCurrentRequest()) {
            return;
        }

        $this->jsData['baseUrl'] = $this->container->getParameter('url');
        $this->jsData['loggedin'] = (int) !$this->user === null;
        $this->jsData['language'] = $this->container->get('language')->getCountryCode();

        if ($this->user) {
            $this->jsData['notification']['role'] = $this->user->getData()['Role'];
            $this->jsData['notification']['id'] = $this->user->getId();
            if ($this->container->get('session')->Acl()->isAllowed('admin_support')) {
                $this->jsData['notification']['support'] = 1;
            }
        }

        $this->buildMenu($this->container->get('request'), $module);
    }

    /**
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('getJSData', [$this, 'getJSData'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    /**
     * Get Js Data.
     *
     * @return string
     */
    public function getJSData()
    {
        if ($this->container->initialized('jsData')) {
            $this->jsData += $this->container->get('jsData');
        }

        return \json_encode($this->jsData);
    }

    /**
     * @param $user
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private function filterUser(&$user)
    {
        $user['Rank'] = $this->container->get('app.user.user')->getUserRank($user['RankPoints']);
        $user['UserSlug'] = $this->container->get('session')->get('userSlug');
        $user['Notifications'] = $this->container->get('doctrine.dbal.default_connection')->fetchAll('SELECT noti.*, (SELECT COUNT(*) FROM users_notification WHERE userID = :userId AND `read` = 0) as unreadCount, (SELECT Avatar FROM users WHERE id = noti.fromUser) as Avatar FROM users_notification noti WHERE userID = :userId ORDER BY id DESC LIMIT 10', [
            'userId' => $user['id'],
        ]);
    }

    /**
     * @param Request $request
     * @param $module
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private function buildMenu(Request $request, $module)
    {
        if ($module === 'backend') {
            $menuDataFetch = $this->container->get('doctrine.dbal.default_connection')->fetchAll('SELECT * FROM core_menu WHERE menuTyp = 3 AND menuActive = 1 ORDER BY menuSort ASC');
        } else {
            $groupID = $this->user === null ? 0 : 1;
            $menuDataFetch = $this->container->get('doctrine.dbal.default_connection')->fetchAll('SELECT * FROM core_menu WHERE menuActive = 1 AND (menuTyp = ' . $groupID . ' OR menuTyp = 2) ORDER BY menuSort ASC');
        }

        $menuData = [];

        foreach ($menuDataFetch as $menu) {
            $menu['menuDefaultName'] = __($menu['menuDefaultName'], 'Menu' . ($module === 'backend' ? '_Backend' : ''), $menu['menuID']);
            $menuData[$menu['menuID']] = $menu;

            $menuData[$menu['menuID']]['active'] = \strpos($request->getRequestUri(), $menu['menuLink']) !== false;
        }

        if (!empty($menuData['server'])) {
            $gsServers = $this->container->get('app.user.user')->getGameserverForMenu();

            $subData = [];

            foreach ($gsServers as $gsServer) {
                $menuLink = 'server/view/' . $gsServer['id'];

                $subData[] = [
                    'id' => null,
                    'menuActive' => 1,
                    'menuID' => 0,
                    'menuClass' => '',
                    'menuDefaultName' => $gsServer['name'],
                    'menuTyp' => 1,
                    'menuSort' => 1,
                    'menuLink' => $menuLink,
                    'menuParent' => '',
                    'active' => \strpos($request->getRequestUri(), $menuLink) === 0,
                ];
            }
            $menuData['server']['sub'] = $subData;
        }

        if (empty($menuData['server']['sub'])) {
            unset($menuData['server']);
        }

        if ($module === 'backend') {
            foreach ($menuData as $key => $menu) {
                if (!$this->container->get('session')->Acl()->isAllowed('admin_' . $key)) {
                    unset($menuData[$key]);
                }
            }
        }

        $this->container->get('twig')->addGlobal('menuItems', $menuData);
    }
}
