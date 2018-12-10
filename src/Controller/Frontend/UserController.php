<?php

namespace GSS\Controller\Frontend;

use Doctrine\DBAL\Connection;
use GSS\Component\Form\Forms\PasswordChangeType;
use GSS\Component\Form\Forms\PasswordResetType;
use GSS\Component\Form\Forms\RegisterAccountType;
use GSS\Component\Form\Forms\SmsVerificationType;
use GSS\Component\HttpKernel\Controller;
use GSS\Component\Security\PasswordEncoder\Bcrypt;
use GSS\Component\Session\FlashMessenger;
use GSS\Component\Session\Session;
use GSS\Component\User\User as UserComponent;
use GSS\Component\User\User;
use GSS\Component\Util;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller User
 * Handles user account actions like register, login.
 *
 *
 * @author Soner Sayakci <***REMOVED***>
 */
class UserController extends Controller
{
    /**
     * @var Util
     */
    private $util;

    /**
     * @var UserComponent
     */
    private $user;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Connection
     */
    private $connection;
    
    /**
     * @var FlashMessenger
     */
    private $flashMessenger;

    /**
     * UserController constructor.
     * @param Util $util
     * @param User $user
     * @param Session $session
     * @param Connection $connection
     * @param FlashMessenger $flashMessenger
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function __construct(Util $util, User $user, Session $session, Connection $connection, FlashMessenger $flashMessenger)
    {
        $this->util = $util;
        $this->user = $user;
        $this->session = $session;
        $this->connection = $connection;
        $this->flashMessenger = $flashMessenger;
    }

    /**
     * @Route("/user")
     * @Route("/user/")
     *
     * @param Bcrypt $bcrypt
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function indexAction(Bcrypt $bcrypt)
    {
        $this->View()->setPageTitle('Mein Profil');

        if ($this->userID === null) {
            return $this->redirectToRoute('index');
        }

        $post = $this->Request()->getPost();

        $this->data['timezones'] = \timezone_identifiers_list();

        if ($this->Request()->isPost()) {
            $updateData = [
                'Skype' => $post['Skype'],
                'Description' => $this->Request()->getPostHtml('Description'),
                'Signatur' => $this->Request()->getPostHtml('Signatur'),
                'timezone' => $this->Request()->getPost('timezone'),
            ];

            if (!empty($post['oldPassword']) && !empty($post['newPassword1']) && !empty($post['newPassword2'])) {
                $userData = $this->session->getUserData();

                if ($bcrypt->verify($userData['Password'], $post['oldPassword'], $userData['Salt'])) {
                    if ($post['newPassword1'] === $post['newPassword2']) {
                        $updateData['Password'] = $bcrypt->crypt($post['newPassword1'], $userData['Salt'])['password'];
                    } else {
                        $this->data['ErrorMessages'][] = __('Deine neuen Passwörter passen nicht', 'User', 'NeuPasswordsAreNotMatching');
                    }
                } else {
                    $this->data['ErrorMessages'][] = __('Dein altes Passwort passt nicht', 'User', 'OldPasswordWrong');
                }
            }

            if (empty($this->data['ErrorMessages'])) {
                $this->flashMessenger->addSuccess('User', __('Dein Profil wurde erfolgreich aktualisiert', 'User', 'ProfilUpdated'));

                $this->connection->update('users', $updateData, ['id' => $this->userID]);

                if (isset($updateData['Password'])) {
                    $this->flashMessenger->addInfo('User', __('Bitte logge dich neu ein mit deinen neuen Passwort', 'User', 'PasswordUpdated'));
                    return $this->redirectToRoute('index');
                }

                return $this->redirectToRoute('gss_frontend_user_index');

            }
        }

        return $this->render('frontend/user/index.twig', $this->data);
    }

    /**
     * @Route("/user/deleteAvatar")
     * Delete Account.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @author Soner Sayakci <***REMOVED***>
     * @throws \Doctrine\DBAL\DBALException
     */
    public function deleteAvatarAction()
    {
        $oldAvatar = $this->session->getUserData('Avatar');
        if (!empty($oldAvatar)) {
            $this->util->deleteAvatar($oldAvatar);
            $this->connection->executeQuery('UPDATE users SET Avatar = NULL WHERE id = ?', [
                $this->userID,
            ]);
        }

        $this->flashMessenger->addSuccess('User', __('Dein Avatar wurde erfolgreich gelöscht', 'User', 'DeleteAvatar'));

        return $this->redirectToRoute('gss_frontend_user_index');
    }

    /**
     * @Route("/user/saveAvatar")
     * New Avatar.
     *
     * @return object|\Symfony\Component\HttpFoundation\Response
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function saveAvatarAction()
    {
        $oldAvatar = $this->session->getUserData('Avatar');

        if (empty($_POST['img']) || empty($_POST['scale']) || empty($_POST['options'])) {
            $this->flashMessenger->addError('Account', __('Avatar konnte nicht gesetzt werden', 'User', 'DeleteAvatarError'));

            return new JsonResponse([]);
        }

        if (!empty($oldAvatar)) {
            $this->util->deleteAvatar($oldAvatar);
        }

        $types = [
            'jpeg' => 'jpg',
            'gif' => 'gif',
            'png' => 'png',
        ];

        $img = $_POST['img'];

        $img = \substr($img, 11);
        $suffix = \substr($img, 0, \strpos($img, ';'));
        $img = \substr($img, \strpos($img, ';') + 8);

        $filePath = \sys_get_temp_dir() . '/' . \uniqid('gss', true) . '.' . $types[$suffix];
        \file_put_contents($filePath, \base64_decode($img));

        try {
            $id = $this->util->createThumbnail($filePath, $_POST['options']);
        } catch (\Exception $e) {
            $this->flashMessenger->addError('Avatar', 'Your avatar file is corrupt. Please save it as new file');


            return new JsonResponse();
        }

        $this->connection->update(
            'users',
            [
                'Avatar' => $id,
            ],
            [
                'id' => $this->userID,
            ]
        );

        $this->flashMessenger->addSuccess('User', 'Dein Avatarbild wurde erfolgreich gesetzt');

        return new JsonResponse();
    }

    /**
     * @Route("/register")
     * Register new user.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function registerAction()
    {
        $this->View()->setPageTitle('Registrieren');

        $form = $this->container->get('form.factory')->create(RegisterAccountType::class);
        $form->handleRequest($this->Request());

        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();
            $post['email'] = \str_replace('@googlemail.com', '@gmail.com', $post['email']);

            if (!$this->user->isRegistered($post['email'])) {
                if (!$this->user->isUsernameUsed($post['username'])) {
                    $this->user->registerUser($post);

                    $this->flashMessenger->addSuccess('Registrierung', __('Du hast dich erfolgreich registriert. Bitte prüfe deine Email-Adresse', 'User', 'SuccessRegistered'));

                    return $this->redirectToRoute('index');
                }

                $this->flashMessenger->addError('Register', __('Dein Username ist bereits vergeben', 'User', 'UsernameTaken'));
            } else {
                $this->flashMessenger->addError('Register', __('Ein Konto ist bereits unter dieser Email registriert!', 'User', 'AlreadyActivated'));
            }
        }

        return $this->render('frontend/user/register.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/login", name="login")
     *
     * Login a user.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return new JsonResponse([$error, $lastUsername]);
    }

    /**
     * @Route("/user/activate/{code}")
     *
     * @param $code
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @author Soner Sayakci <***REMOVED***>
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function activateAction($code = null)
    {
        // if code is empty redirect to index
        if (empty($code)) {
            return $this->redirectToRoute('index');
        }

        if ($this->connection->fetchColumn('SELECT 1 FROM blocked_tasks WHERE Value = ? and Method = "activation"', [$code])) {
            $this->connection->delete('blocked_tasks', ['Value' => $code, 'Method' => 'activation']);
            $this->flashMessenger->addSuccess('Account', __('Dein Account wurde erfolgreich verifiziert, du kannst dich nun einloggen', 'Login', 'VerificationSuccess'));
        } else {
            $this->flashMessenger->addError('Account', __('Dein Aktivierungscode konnte nicht gefunden werden. Bitte fordere einen neuen an', 'Login', 'VerificationError'));
        }

        return $this->redirectToRoute('index');
    }

    /**
     * @Route("/user/reset")
     * @Route("/user/reset/{code}")
     * Password reset.
     *
     * @param string $code
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function resetAction($code = '')
    {
        $this->View()->setPageTitle('Passwort vergessen?');

        if ($this->userID) {
            return $this->redirectToRoute('gss_frontend_user_index');
        }

        /*
         * Wenn Leer dann die Form
         */
        if (empty($code)) {
            $passwordReset = $this->container->get('form.factory')->create(PasswordResetType::class);
            $passwordReset->handleRequest($this->container->get('request'));

            if ($passwordReset->isSubmitted() && $passwordReset->isValid()) {
                $formData = $passwordReset->getData();
                $response = $this->user->resetPassword($formData['email']);

                if ($response['success']) {
                    $this->flashMessenger->addSuccess('Account', $response['message']);

                    return $this->redirectToRoute('index');
                }

                $passwordReset->get('email')->addError(new FormError($response['message']));
            }

            return $this->render('frontend/user/pw_forget.twig', [
                'form' => $passwordReset->createView(),
            ] + $this->data);
        }

        $passwordChange = $this->container->get('form.factory')->create(PasswordChangeType::class);
        $passwordChange->handleRequest($this->Request());

        if ($passwordChange->isSubmitted() && $passwordChange->isValid()) {
            $formData = $passwordChange->getData();

            $response = $this->user->setUserPasswordFromReset($code, $formData['password']);

            if ($response['success']) {
                $this->flashMessenger->addSuccess('Account', $response['message']);

                return $this->redirectToRoute('index');
            }

            $passwordChange->addError(new FormError($response['message']));
        }

        return $this->render('frontend/user/pw_reset.twig', [
            'form' => $passwordChange->createView(),
        ] + $this->data);
    }

    /**
     * @Route("/user/logout")
     * Logout a user.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function logoutAction()
    {
        $this->session->invalidate();

        return $this->redirectToRoute('index');
    }

    /**
     * @return Response
     * @Route("/user/activateSms")
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function activateSmsAction()
    {
        if (empty($this->userID)) {
            return $this->redirectToRoute('index');
        }

        $this->View()->setPageTitle('Activate Account');
        $data = [];

        if ($this->Request()->get('reset')) {
            $this->session->remove('phoneCode');
            $this->session->remove('phoneNumber');

            return $this->redirectToRoute('gss_frontend_user_activate');
        }

        $smsTask = $this->connection->fetchAssoc('SELECT * FROM blocked_tasks WHERE Email = ? AND Method = "sms"', [
            $this->userID,
        ]);

        if ($smsTask['Value'] >= 3) {
            $data['limitReached'] = true;
        }

        $smsVerification = $this->container->get('form.factory')->create(SmsVerificationType::class);
        $smsVerification->handleRequest($this->Request());

        if ($this->session->has('phoneCode')) {
            $data['smsSend'] = true;

            if ($this->Request()->getPost('code')) {
                if ($this->Request()->getPost('code') == $this->session->get('phoneCode')) {
                    $this->connection->update('users', [
                        'sms' => $this->session->get('phoneNumber'),
                    ], [
                        'id' => $this->userID,
                    ]);
                    $this->flashMessenger->addSuccess('Verification', __('Your Account has been activated', 'User', 'AccountActivated'));

                    $this->session->remove('phoneCode');
                    $this->session->remove('phoneNumber');

                    return $this->redirectToRoute('gss_frontend_shop_index');
                }

                $this->flashMessenger->addError('Verification', __('Your code is wrong', 'User', 'CodeInvalid'));
            }
        } else {
            if ($smsVerification->isSubmitted() && $smsVerification->isValid()) {
                $data = $smsVerification->getData();
                $number = $data['mobilenumber'];

                $number = \str_replace(' ', '', $number);
                if (\preg_match('/\+\d{8}/', $number)) {
                    $code = \random_int(100000, 999999);

                    if ($this->connection->fetchColumn('SELECT 1 FROM users WHERE sms = ?', [$number])) {
                        $data['invalidNumber'] = true;
                    } else {
                        if (empty($smsTask['Value'])) {
                            $smsTask['Value'] = 0;
                        }
                        ++$smsTask['Value'];

                        if (empty($smsTask['id'])) {
                            $this->connection->insert('blocked_tasks', [
                                'Email' => $this->userID,
                                'Method' => 'sms',
                                'Value' => $smsTask['Value'],
                                'TTL' => 86400,
                            ]);
                        } else {
                            $this->connection->update('blocked_tasks', [
                                'Value' => $smsTask['Value'],
                            ], [
                                'id' => $smsTask['id'],
                            ]);
                        }

                        $success = $this->container->get('app.user.sms')->sendMessage($number, 'Code: ' . $code);

                        if ($success) {
                            $this->session->set('phoneNumber', $number);
                            $this->session->set('phoneCode', $code);

                            $this->flashMessenger->addSuccess('Verification', __('SMS has been send', 'User', 'SMSSend'));
                            $data['smsSend'] = true;
                        } else {
                            $this->flashMessenger->addError('Verification', __('SMS couldnt send. Please try later again', 'User', 'SMSSendError'));
                        }
                    }
                } else {
                    $data['invalidNumber'] = true;
                }
            }

            $data['form'] = $smsVerification->createView();
        }

        return $this->render('frontend/user/activateSms.twig', $data + $this->data);
    }

    /**
     * @Route("/user/{name}")
     * Show user profiles from other users.
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function viewAction($name)
    {
        if (empty($name)) {
            return $this->redirectToRoute('index', [], 404);
        }

        $data = $this->container->get('rewrite_manager')->getRewriteParamsByUrl('user/' . $name);

        if (isset($data['forwardParams'])) {
            $userID = \json_decode($data['forwardParams'], true)['userID'];
        } else {
            throw new NotFoundHttpException();
        }

        $this->data['viewedUser'] = $this->connection->fetchAssoc('
          SELECT
            *,
            (SELECT COUNT(*) FROM forum_entries WHERE userID = users.id) AS entriesCount,
            (SELECT COUNT(*) FROM likes WHERE liked_user = users.id) AS likes
          FROM
              users
          WHERE id = ?', [
            $userID,
        ]);

        // do 404, if user doesnt exists
        if (empty($this->data['viewedUser'])) {
            throw new NotFoundHttpException();
        }

        $this->data['viewedUser']['rank'] = $this->user->getUserRank($this->data['viewedUser']['RankPoints']);

        $this->data['viewedUser']['server'] = $this->user->getGameserverData($userID);

        $this->connection->executeQuery('UPDATE users SET Visits = Visits + 1 WHERE id = ?', [$userID]);

        $this->data['pageTitle'] = __('Benutzerprofile von ', 'User', 'UserProfileFrom') . $this->data['viewedUser']['Username'];

        return $this->render('frontend/user/view.twig', $this->data);
    }
}
