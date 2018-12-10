<?php

namespace GSS\Controller\Frontend;

use GSS\Component\Commerce\GP;
use GSS\Component\Hosting\Gameserver\Daemon;
use GSS\Component\Hosting\Gameserver\Gameserver;
use GSS\Component\HttpKernel\Controller;
use GSS\Component\Security\Permission;
use GSS\Component\Twig\ExtensionGlobals;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class Server.
 */
class ServerController extends Controller
{
    /**
     * @Route("/server/adminDelete/{gsID}")
     * Admin Delete
     *
     * @param $gsID
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function adminDeleteAction($gsID)
    {
        $gs = Gameserver::createServer($this->container, $gsID);

        if ($this->container->get('session')->Acl()->isAllowed('admin_gameserver_delete')) {
            $gs->addTask('GSDelete');
        }

        return $this->redirectToIndex($gs);
    }

    /**
     * @Route("/server/addTask/{gsID}/{task}")
     *
     * @param null $gsID
     * @param null $task
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addTaskAction($gsID = null, $task = null)
    {
        $gs = Gameserver::createServer($this->container, $gsID);
        $gs->addTask($task, $this->Request()->getPost());

        return $this->redirectToIndex($gs);
    }

    /**
     * @Route("/server/deleteRight/{gsID}/{user}")
     * Delete Rights
     *
     * @param null $gsID
     * @param null $user
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteRightAction($gsID = null, $user = null)
    {
        $gs = Gameserver::createServer($this->container, $gsID);

        $rightUser = $this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT id FROM users WHERE Email = ?', [$user]);

        if (!empty($rightUser)) {
            if ($rightUser != $gs->getOwner()) {
                $this->container->get('doctrine.dbal.default_connection')->executeQuery('DELETE FROM users_to_gameserver WHERE userID = ? AND gameserverID = ?', [
                    $rightUser,
                    $gs->getId(),
                ]);
            }
        }

        return $this->redirectToIndex($gs);
    }

    /**
     * Javascript Communication Action.
     *
     * @Route("/server/api")
     */
    public function apiAction()
    {
        $this->data = [
            'result' => 'error',
            'message' => 'Invalid Arguments passed',
            'data' => [],
        ];

        if (!$this->Request()->isXmlHttpRequest()) {
            return $this->redirectToRoute('index');
        }

        $action = $this->Request()->getPost('action', null);
        $gsID = $this->Request()->getPost('gsID');

        if (!empty($action) && !empty($gsID)) {
            $gs = Gameserver::createServer($this->container, $gsID);
            $this->container->get('app.hosting.gameserver.db.server')->setHost($gs->getHost());
            switch ($action) {
                case 'getConfig':
                    $path = $gs->getConfigPathFromName($this->Request()->getPost('config'));

                    if ($path == false) {
                        $this->data['message'] = __(
                            'Es konnte keine Konfigurationsdatei gefunden werden.',
                            'Server',
                            'ConfigNotFound'
                        );
                    } else {
                        $fileContent = $gs->getSSH()->get($gs->calcServerDirectory($path));

                        if ($fileContent == 'false' || $fileContent == false) {
                            $fileContent = '';
                        } elseif (\json_encode($fileContent) === false) {
                            $this->data['data'] = __('Could not read file. Please use ftp to adjust your settings', 'Server', 'FileIsNotUnicode');
                            break;
                        }

                        $this->data['data'] = $fileContent;
                        $this->data['result'] = 'success';
                    }
                    break;
                case 'saveConfig':
                    $config = $this->Request()->getPost('config');
                    $path = $gs->getConfigPathFromName($config);

                    if ($path == false) {
                        $this->data['message'] = __(
                            'Es konnte keine Konfigurationsdatei gefunden werden.',
                            'Server',
                            'ConfigNotFound'
                        );
                    } else {
                        if ($gs->checkConfigFile($config, $_POST['configValue'])) {
                            $gs->getSSH()->put($gs->calcServerDirectory($path), $_POST['configValue']);
                            $this->data['message'] = __(
                                'Die Konfigurationsdatei wurde erfolgreich gespeichert.',
                                'Server',
                                'ConfigSaved'
                            );
                            $this->data['result'] = 'success';
                        } else {
                            $this->data['message'] = __(
                                'Die Validierung der Configurationsdatei ist fehlgeschlagen.',
                                'Server',
                                'ConfigCheckFailed'
                            );
                            $this->data['result'] = 'error';
                        }
                    }
                    break;

                case 'getDatabaseInfo':
                    $dbID = $this->Request()->getPost('dbID');

                    /*
                     * Have the User Permission to this Database?
                     */
                    if (Permission::isDatabaseFromServer($this->container->get('doctrine.dbal.default_connection'), $dbID, $gsID)) {
                        $this->data['result'] = 'success';
                        $this->data['data'] = $this->container->get('doctrine.dbal.default_connection')->fetchAssoc('SELECT * FROM gameserver_database WHERE id = ? AND gameserverID = ?', [
                            $dbID,
                            $gsID,
                        ]);
                    } else {
                        $this->data['result'] = 'error';
                    }
                    break;
                case 'saveDatabase':
                    $dbID = $this->Request()->getPost('dbID');
                    $form = $this->Request()->getPost('form');

                    if (Permission::isDatabaseFromServer($this->container->get('doctrine.dbal.default_connection'), $dbID, $gsID)) {
                        $this->container->get('app.hosting.gameserver.db.server')->updateDatabase($dbID, $form);

                        $this->data['result'] = 'success';
                        $this->container->get('flash.messenger')->addSuccess(
                            'Datenbank',
                            __('Datenbank wurde erfolgreich aktualisiert', 'Server', 'DatabaseUpdated')
                        );
                    } else {
                        $this->data['result'] = 'error';
                        $this->data['message'] = 'Access Denied';
                        $this->container->get('flash.messenger')->addError('Datenbank', 'Access Denied');
                    }
                    break;

                case 'getFTPInfo':
                    $ftpID = $this->Request()->getPost('ftpID');

                    /*
                     * Have the User Permission to this FTP?
                     */
                    if (Permission::isFTPFromServer($ftpID, $gsID)) {
                        $this->data['result'] = 'success';
                        $this->data['data'] = $this->container->get('doctrine.dbal.default_connection')->fetchAssoc('SELECT * FROM ftpuser WHERE userid = ?', [
                            $ftpID,
                        ]);

                        $this->data['data']['homedir'] = \str_replace(
                            $gs->calcServerDirectory(''),
                            '',
                            $this->data['data']['homedir']
                        );
                    } else {
                        $this->data['result'] = 'error';
                        $this->data['message'] = 'Access Denied';
                        $this->container->get('flash.messenger')->addError('Datenbank', 'Access Denied');
                    }
                    break;

                case 'saveFTP':
                    $ftpID = $this->Request()->getPost('ftpID');
                    $form = $this->Request()->getPost('form');

                    /*
                     * Have the User Permission to this FTP?
                     */
                    if (Permission::isFTPFromServer($ftpID, $gsID)) {
                        $this->container->get('app.hosting.gameserver.ftp')->updateFTPAccount($gs, $ftpID, $form);
                        $this->data['result'] = 'success';
                        $this->container->get('flash.messenger')->addSuccess(
                            'FTP',
                            __('FTP Account aktualisiert', 'Server', 'FTPAccountUpdated')
                        );
                    } else {
                        $this->data['result'] = 'error';
                        $this->data['message'] = 'Access Denied';
                        $this->container->get('flash.messenger')->addError('FTP', 'Access Denied');
                    }

                    break;
                case 'addRight':
                    $data = $this->Request()->getPost('data');
                    $currentUser = $this->userID;

                    /*
                     * Block if Owner isnt currentUser
                     */
                    if ($currentUser != $gs->getOwner()) {
                        $this->data['result'] = 'error';
                        $this->data['message'] = 'Du hast keine Berechtigung dafür';
                    } else {
                        $rightUser = $this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT id FROM users WHERE Email = ?', [$data['user']]);

                        unset($data['user']);

                        /*
                         * Remove for Security Reason
                         */
                        if (isset($data['all'])) {
                            unset($data['all']);
                        }

                        $data['show'] = 1;

                        if (!empty($rightUser)) {
                            $userToGameserver = $this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT id FROM users_to_gameserver WHERE userID = ? AND gameserverID = ?', [$rightUser, $gs->getId()]);

                            if (!empty($userToGameserver)) {
                                $this->container->get('doctrine.dbal.default_connection')->update('users_to_gameserver', [
                                    'Rights' => \json_encode($data),
                                ], ['id' => $userToGameserver]);

                                $this->data['result'] = 'success';
                                $this->data['message'] = __('Der Benutzer wurde aktualisiert', 'Server', 'UserUpdated');
                            } else {
                                $this->container->get('doctrine.dbal.default_connection')->executeQuery('INSERT INTO users_to_gameserver (userID, gameserverID, Rights) VALUES(?,?,?)', [
                                    $rightUser,
                                    $gs->getId(),
                                    \json_encode($data),
                                ]);

                                $this->data['result'] = 'success';
                                $this->data['message'] = __('Der Benutzer wurde zum Gameserver hinzugefügt', 'Server', 'UserAddedToGameserver');
                            }
                        } else {
                            $this->data['result'] = 'error';
                            $this->data['message'] = __('Der Benutzer konnte nicht gefunden werden', 'Server', 'UserCouldNotFound');
                        }
                    }
                    break;
                case 'getTaskInfo':
                    $this->data['state'] = $gs->getState();
                    break;

                case 'usage':
                    $this->data['result'] = $gs->getStats();
                    break;
                default:
                    break;
            }
        }

        return new JsonResponse($this->data);
    }

    /**
     * View Action.
     *
     * @Route("/server/view/{gsID}")
     *
     * @param null $gsID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction($gsID = null)
    {
        if (empty($gsID)) {
            return $this->redirectToRoute('index');
        }

        $this->View()->setPageTitle('Gameserver Verwaltung');

        $gs = Gameserver::createServer($this->container, $gsID);
        $this->container->get('app.hosting.gameserver.db.server')->setHost($gs->getHost());
        $daemon = new Daemon($this->container, $gs->getHost());

        $this->data['gs'] = $gs->getData();
        $this->data['gsVersions'] = $gs->getVersions();
        $this->data['gsConfigs'] = $gs->getConfigFiles();
        $this->data['ftpUsers'] = $this->container->get('app.hosting.gameserver.ftp')->getFTPAccounts($gsID);
        $this->data['databases'] = $this->container->get('app.hosting.gameserver.db.server')->getDatabasesByGameserver($gsID);
        $this->data['upgrades'] = $gs->getUpgrades();
        $this->data['rights'] = $gs->getRights();
        $this->data['gsProperties'] = $gs->getProperties();
        $this->data['gsForm'] = $gs->getForm();
        $this->data['gsObj'] = $gs;
        $this->data['cloudflare_domains'] = $this->container->getParameter('cloudflare.use_domains');

        $this->data['breadcrumb'] = [
            [
                'name' => 'Server',
                'link' => '#',
            ],
            [
                'name' => $this->data['gs']['IP'] . ':' . $this->data['gs']['port'],
                'link' => '#',
            ],
        ];

        $jsData = [];
        $jsData['gsID'] = $this->data['gs']['id'];
        $jsData['hostname'] = $daemon->getSocketUrl();
        $jsData['token'] = $daemon->getToken($gs->getId());
        $jsData['state'] = $gs->getState();

        $this->container->set('jsData', $jsData);

        return $this->render('frontend/server/index.twig', $this->data);
    }

    /**
     * @Route("/server/deleteFTP/{gsID}/{ftpName}")
     * Delete FTP.
     *
     * @param $gsID
     * @param $ftpName
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteFTPAction($gsID, $ftpName)
    {
        $gs = Gameserver::createServer($this->container, $gsID);

        if ($this->container->get('app.hosting.gameserver.ftp')->removeFTPAccount($gsID, $ftpName)) {
            $this->container->get('flash.messenger')->addSuccess('Gameserver', 'Der FTP Benutzer wurde erfolgreich gelöscht');
        } else {
            $this->container->get('flash.messenger')->addError('Gameserver', 'Der FTP Benutzer konnte nicht gelöscht werden');
        }

        return $this->redirectToIndex($gs);
    }

    /**
     * @Route("/server/deleteDB/{gsID}/{dbName}")
     * Delete Database.
     *
     * @param $gsID
     * @param $dbName
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteDBAction($gsID, $dbName)
    {
        $gs = Gameserver::createServer($this->container, $gsID);

        $dbServer = $this->container->get('app.hosting.gameserver.db.server')->setHost($gs->getHost());

        if ($dbServer->removeDatabaseAccount($gsID, $dbName)) {
            $this->container->get('flash.messenger')->addSuccess('Gameserver', 'Die Datenbank wurde erfolgreich gelöscht');
        } else {
            $this->container->get('flash.messenger')->addError('Gameserver', 'Die Datenbank konnte nicht gelöscht werden');
        }

        return $this->redirectToIndex($gs);
    }

    /**
     * @Route("/server/deleteServer/{gsID}")
     * Delete Passive Server.
     *
     * @param $gsID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteServerAction($gsID)
    {
        $gs = Gameserver::createServer($this->container, $gsID);

        if ($gs->getOwner() == $this->userID) {
            if ($gs->getTyp() == 1) {
                $gs->addTask('GSDelete');
            } else {
                $gs->deleteActiveServer();
            }

            $this->container->get('flash.messenger')->addSuccess(
                'Gameserver',
                __('Dein Gameserver wird in kürze gelöscht', 'Server', 'SuccessDeleteMessage')
            );
        } else {
            $this->container->get('flash.messenger')->addError(
                'Gameserver',
                __('Du kannst diesen Gameserver nicht löschen', 'Server', 'DeleteGameserver')
            );
        }

        return $this->redirectToIndex($gs);
    }

    /**
     * Extend a given server.
     *
     * @Route("/server/extend/{gsID}/{variantId}")
     *
     * @param int $gsID
     * @param int $variantId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function extendAction($gsID = 0, $variantId = 0)
    {
        $gs = Gameserver::createServer($this->container, $gsID);

        if ($gs->getOwner() == $this->userID) {
            if ($gs->extendServer($variantId)) {
                $this->container->get('flash.messenger')->addSuccess(
                    'Gameserver',
                    __('Dein Server wurde erfolgreich geupgradet', 'Server', 'SuccessUpgraded')
                );
            } else {
                $this->container->get('flash.messenger')->addSuccess(
                    'Gameserver',
                    __('Dein Server konnte nicht geupgradet werden', 'Server', 'ErrorUpgradet')
                );
            }
        } else {
            $this->container->get('flash.messenger')->addError(
                'Gameserver',
                __('Du kannst diesen Server nicht upgraden', 'Server', 'DeniedServerUpgrade')
            );
        }

        return $this->redirectToIndex($gs);
    }

    /**
     * @Route("/server/saveProperties/{gsID}")
     *
     * @param null $gsID
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function savePropertiesAction($gsID = null)
    {
        $gs = Gameserver::createServer($this->container, $gsID);
        foreach ($_POST as &$value) {
            $value = \explode(' ', $value)[0];
        }

        $gs->setProperties($_POST);

        return $this->redirectToIndex($gs);
    }

    /**
     * @Route("/server/extendTime/{gsID}", methods={"POST"})
     *
     * @param null $gsID
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function extendTimeAction($gsID = null)
    {
        $gs = Gameserver::createServer($this->container, $gsID);

        if (!$this->Request()->request->has('verlaegern')) {
            return $this->redirectToIndex($gs);
        }

        switch ($this->Request()->request->get('verlaegern')) {
            default:
            case 1:
                $timeLength = \strtotime('+7days', $gs->getDuration());
                $gpCoast = \ceil(($gs->getPrice() / 4) * 1.1);
                break;

            case 2:
                $timeLength = \strtotime('+14days', $gs->getDuration());
                $gpCoast = \ceil(($gs->getPrice() / 2) * 1.05);
                break;

            case 3:
                $timeLength = \strtotime('+1month', $gs->getDuration());
                $gpCoast = $gs->getPrice();
                break;

            case 4:
                $timeLength = \strtotime('+2month', $gs->getDuration());
                $gpCoast = \ceil($gs->getPrice() * 2 * 0.98);
                break;

            case 5:
                $timeLength = \strtotime('+3month', $gs->getDuration());
                $gpCoast = \ceil($gs->getPrice() * 3 * 0.95);
                break;
        }

        if ($this->container->get('session')->getUserData('GP') >= $gpCoast) {
            $this->container->get('doctrine.dbal.default_connection')->executeQuery(
                'UPDATE users SET RankPoints = RankPoints + 1 WHERE id = ?',
                [$this->userID]
            );

            /*
             * Set Duration
             */
            $this->container->get('doctrine.dbal.default_connection')->update(
                'gameserver',
                ['Duration' => $timeLength],
                ['id' => $gs->getId()]
            );

            $this->container->get(GP::class)->removePointsFromUser(
                $this->userID,
                $gpCoast,
                __('Server Verlängert', 'Server', 'ServerDurationAdded')
            );

            $this->container->get('flash.messenger')->addSuccess(
                'Gameserver',
                __('Gameserver erfolgreich verlängert', 'Server', 'ServerDurationAddedSuccess')
            );
        } else {
            $this->container->get('flash.messenger')->addError(
                'Gameserver',
                __(
                    'Du hast nicht genügend GP Punkte um den Gameserver zu verlängern',
                    'Server',
                    'NotEnoughtGPForDuration'
                )
            );
        }

        return $this->redirectToIndex($gs);
    }

    /**
     * @Route("/server/setDomain/{gsID}")
     *
     * @param null $gsID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function setDomainAction($gsID = null)
    {
        if (!$this->Request()->isXmlHttpRequest()) {
            return $this->redirectToRoute('index');
        }

        $gs = Gameserver::createServer($this->container, $gsID);

        $domain = $this->Request()->getPost('domain');
        $subdomain = $this->Request()->getPost('subdomain');

        if (empty($subdomain)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid usage',
            ]);
        }

        if (!$this->container->get('app.cloudflare_api')->isSubdomainUsed($domain, $subdomain)) {
            try {
                $bool = $this->container->get('app.cloudflare_api')->registerSubdomain($gs, $domain, $subdomain);
            } catch (\Exception $e) {
                $bool = false;
            }

            return new JsonResponse([
                'success' => $bool,
                'message' => $bool ? __('Die Subdomain ist nun in kürze verfügbar', 'Cloudflare', 'SubdomainReady') : __('Es ist ein Fehler aufgetreten. Bitte versuche einen andere Subdomain', 'Cloudflare', 'SubdomainError'),
            ]);
        }

        return new JsonResponse([
                'success' => false,
                'message' => __('Diese Subdomain ist bereits belegt', 'Cloudflare', 'SubdomainTaken'),
            ]);
    }

    /**
     * @Route("/server/setName/{gsID}")
     *
     * @param null $gsID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function setNameAction($gsID = null)
    {
        if (!$this->Request()->isXmlHttpRequest()) {
            return $this->redirectToRoute('index');
        }

        $gs = Gameserver::createServer($this->container, $gsID);

        $name = $this->Request()->getPost('name');

        if (empty($name)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid usage',
            ]);
        }

        $this->get('doctrine.dbal.default_connection')->update('gameserver', ['name' => $name], ['id' => $gs->getId()]);

        return new JsonResponse([
            'success' => true,
            'message' => 'Name has been changed',
        ]);
    }

    /**
     * @Route("/server/saveAccount/{gsID}")
     *
     * @param null $gsID
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function saveAccountAction($gsID = null)
    {
        $gs = Gameserver::createServer($this->container, $gsID);
        $this->container->get('app.hosting.gameserver.db.server')->setHost($gs->getHost());
        $postData = $this->Request()->getPost();

        if (empty($postData)) {
            return $this->redirectToIndex($gs);
        }

        if ($postData['accountType'] == '0') {
            $this->container->get('app.hosting.gameserver.ftp')->addFTPAccount($gs, $postData);
        } else {
            $this->container->get('app.hosting.gameserver.db.server')->addDatabase($gsID, $postData);
        }

        return $this->redirectToIndex($gs);
    }

    private function redirectToIndex(Gameserver $gs)
    {
        return $this->redirect('/server/' . $gs->getShort());
    }
}
