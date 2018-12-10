<?php

namespace GSS\Component\User;

use Cocur\Slugify\Slugify;
use GSS\Component\Commerce\GP;
use GSS\Component\HttpKernel\Request;
use GSS\Component\Security\User as UserModel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class User
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * User constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $email
     *
     * @return bool
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function isRegistered($email): bool
    {
        if (empty($email)) {
            return false;
        }

        return $this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT 1 FROM users WHERE Email LIKE ?', [
            $email,
        ]);
    }

    /**
     * @param $username
     *
     * @return bool
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function isUsernameUsed($username): bool
    {
        return $this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT 1 FROM users WHERE Username LIKE ?', [
            $username,
        ]);
    }

    /**
     * @param array $data
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function registerUser(array $data = []): void
    {
        $hashedPassword = $this->container->get('app.security.password_encoder.bcrypt')->crypt($data['password']);
        $randomNumber = \uniqid('gss', true);

        $partner = $this->container->get('session')->get('Partner');

        if (!empty($partner)) {
            $this->container->get(GP::class)->addPointsToUser($partner, $this->container->getParameter('gppoints.inviteregister'), 'GS Banner Register');
        }

        $user = [
            'Username' => $data['username'],
            'Password' => $hashedPassword['password'],
            'Salt' => $hashedPassword['salt'],
            'Email' => $data['email'],
            'RegisterDate' => \date('Y-m-d'),
            'Language' => $this->container->get('language')->getCountryCode(),
            'Permissions' => \json_encode([]),
        ];

        $this->container->get('doctrine.dbal.default_connection')->insert('users', $user);
        $insertId = $this->container->get('doctrine.dbal.default_connection')->lastInsertId();

        $slug = $this->container->get(Slugify::class)->slugify($data['username']);

        $this->container->get('rewrite_manager')->addRewrite('user/' . $slug, 'user', 'view', [
            'userID' => $insertId,
        ]);

        $gpComponent = $this->container->get(GP::class);
        $gpComponent->addPointsToUser($insertId, $this->container->getParameter('gppoints.startgp'), 'Start GP');

        if (!isset($data['fb'])) {
            /*
             * Insert ActiveEmail
             */
            $this->container->get('doctrine.dbal.default_connection')->insert('blocked_tasks', [
                'Email' => $data['email'],
                'Method' => 'activation',
                'Value' => $randomNumber,
                'TTL' => \time() + 86400,
            ]);

            $htmlData = $this->container->get('twig')->render('email/register.twig', [
                'activateLink' => $randomNumber,
                'username' => $data['username'],
            ]);

            $mail = new \Swift_Message();
            $mail
                ->setFrom($this->container->getParameter('email.sender'), $this->container->getParameter('email.sendername'))
                ->setTo($data['email'], $data['username'])
                ->setSubject(__('Ihre Registrierung bei Gameserver-Sponsor', 'User', 'Email/Register/Subject'))
                ->setBody($htmlData, 'text/html', 'UTF-8');

            $this->container->get('mailer')->send($mail);
        }
    }

    /**
     * @param Request $request
     * @param string  $email
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function loginWithEmail(Request $request, string $email): void
    {
        $user = $this->container->get('doctrine.dbal.default_connection')->fetchAssoc('SELECT * FROM users WHERE Email = :name', [
            'name' => $email,
        ]);

        if (!empty($user['Inhibition'])) {
            $this->container->get('flash.messenger')->addError('Login', $user['Inhibition']);

            return;
        }

        $userModel = new UserModel($user);
        $token = new UsernamePasswordToken($userModel, $userModel->getPassword(), 'main', $userModel->getRoles());
        $this->container->get('security.token_storage')->setToken($token);
        $this->container->get('session')->set('_security_secured_area', \serialize($token));

        $event = new InteractiveLoginEvent($request, $token);
        $this->container->get('event_dispatcher')->dispatch('security.interactive_login', $event);
    }

    /**
     * Returns gameservers for menu
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getGameserverForMenu(): array
    {
        $userID = $this->container->get('session')->getUserID();

        if (!$userID) {
            return [];
        }

        $gsData = $this->container->get('doctrine.dbal.default_connection')->fetchAll('
            SELECT
            gs.id,
            gs.name,
            gsIP.IP,
            gs.port,
            products.internalName as game,
            gs.slot
            FROM users_to_gameserver
            INNER JOIN gameserver gs ON(gs.id = users_to_gameserver.gameserverID)
            INNER JOIN gameroot_ip gsIP ON(gsIP.gamerootID = gs.gamerootID)
            INNER JOIN products ON(products.id = gs.productID)

            WHERE users_to_gameserver.userID = ?
        ', [$this->container->get('session')->getUserID()]);

        foreach ($gsData as &$gs) {
            $gs['IP'] = \str_replace('.', '_', $gs['IP']);
        }

        return $gsData;
    }

    /**
     * Returns gameservers for user view
     *
     * @param $userId
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getGameserverData($userId): array
    {
        return $this->container->get('doctrine.dbal.default_connection')->fetchAll('
            SELECT
            gs.id,
            gsIP.ip,
            gs.port,
            gs.slot,
            gsProduct.name AS productName,
            gsProduct.img AS image
            FROM users_to_gameserver
            LEFT JOIN gameserver gs ON(gs.id = users_to_gameserver.gameserverID)
            LEFT JOIN gameroot_ip gsIP ON(gsIP.gamerootID = gs.gamerootID)
            LEFT JOIN products gsProduct ON(gsProduct.id = gs.productID)

            WHERE users_to_gameserver.userID = ?', [
            $userId,
        ]);
    }

    /**
     * @param string $email
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function resetPassword($email): array
    {
        $returnArray = [
            'success' => false,
            'message' => 'Unknown',
        ];

        if ($this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT 1 FROM blocked_tasks WHERE Email = ? AND Method = ?', [$email, 'forget_password']) == 0) {
            $userData = $this->getUserDataByEmail($email);
            if (!empty($userData['Email'])) {
                $id = \uniqid('gss', true);
                $this->container->get('doctrine.dbal.default_connection')->insert('blocked_tasks', [
                    'Email' => \strtolower($email),
                    'Method' => 'forget_password',
                    'Value' => $id,
                    'TTL' => \strtotime('+1 day'),
                ]);

                $data = [
                    'username' => $userData['Username'],
                    'code' => $id,
                ];

                $mail = new \Swift_Message();
                $mail
                    ->setFrom($this->container->getParameter('email.sender'), $this->container->getParameter('email.sendername'))
                    ->setSubject(
                        __('Password Vergessen', 'User', 'ForgotPasswordMail')
                    )
                    ->setTo($email, $userData['Username'])
                    ->setBody($this->container->get('twig')->render('email/password_forget.twig', $data), 'text/html', 'UTF-8');

                $this->container->get('mailer')->send($mail);

                $returnArray['success'] = true;
                $returnArray['message'] = __('Es wurde eine E-Mail zum zurücksetzen, deines Kontos versendet', 'User', 'MailSend');
            } else {
                $returnArray['message'] = __('Diese E-Mail Adresse ist kein User zugewiesen', 'User', 'CouldNotFoundUser');
            }
        } else {
            $returnArray['message'] = __('Du hast bereits heute eine Passwort Vergessen Anfrage versendet', 'User', 'AlreadySendTodayForgotPassword');
        }

        return $returnArray;
    }

    /**
     * @param $code
     * @param $password
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function setUserPasswordFromReset($code, $password): array
    {
        $returnArray = [
            'success' => false,
            'message' => 'Unknown',
        ];

        if ($this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT COUNT(*) FROM blocked_tasks WHERE Value = ? AND Method = ?', [$code, 'forget_password'])) {
            $TaskData = $this->container->get('doctrine.dbal.default_connection')->fetchAssoc('SELECT * FROM blocked_tasks WHERE Value = ? AND Method = ?', [$code, 'forget_password']);

            $data = [];
            $hashedPassword = $this->container->get('app.security.password_encoder.bcrypt')->crypt($password);
            $data['Password'] = $hashedPassword['password'];
            $data['Salt'] = $hashedPassword['salt'];

            $this->container->get('doctrine.dbal.default_connection')->delete('blocked_tasks', [
                'Value' => $code,
                'Method' => 'forget_password',
            ]);

            $this->container->get('doctrine.dbal.default_connection')
                ->update('users', $data, [
                    'Email' => $TaskData['Email'],
                ]);

            $returnArray['success'] = true;
            $returnArray['message'] = __('Dein Passwort wurde erfolgreich gespeichert. Du kannst dich nun damit einloggen', 'User', 'NewPasswordSaved');
        } else {
            $returnArray['message'] = __('Es konnte kein Benutzerkonto, zu dein Code zugeordnet werden', 'User', 'CouldntFoundCode');
        }

        return $returnArray;
    }

    /**
     * @param string $email
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getUserDataByEmail(string $email)
    {
        return $this->container->get('doctrine.dbal.default_connection')->fetchAssoc('SELECT * FROM users WHERE Email = ?', [$email]);
    }

    /**
     * @param int    $id
     * @param string $key
     * @param string $value
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function setData(int $id, string $key, $value)
    {
        $this->container->get('doctrine.dbal.default_connection')->update('users', [$key => $value], ['id' => $id]);
    }

    /**
     * Returns gameservers for select
     *
     * @param int $userID
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getGameserver(int $userID): array
    {
        return $this->container->get('doctrine.dbal.default_connection')->fetchAll(
            'SELECT
                id, concat((SELECT IP FROM gameroot_ip WHERE id = gameserver.gameRootIpID), ":", gameserver.port) AS string
            FROM
                gameserver
            WHERE userID = ?',
            [$userID]
        );
    }

    /**
     * @param int $points
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getUserRank(int $points): array
    {
        $language = $this->container->get('language')->getCountryCode();
        $cackeKey = 'rankName_' . $language . '_' . $points;

        $cached = $this->container->get('cache')->get($cackeKey);

        if (!$cached) {
            $ranks = [
                [
                    'name' => 'Neuling',
                    'value' => 0,
                ],
                [
                    'name' => 'Anfänger',
                    'value' => 5,
                ],
                [
                    'name' => 'Grünschnabel',
                    'value' => 10,
                ],
                [
                    'name' => 'Lehrling',
                    'value' => 20,
                ],
                [
                    'name' => 'Fortgeschrittener',
                    'value' => 30,
                ],
                [
                    'name' => 'Anwärter',
                    'value' => 40,
                ],
                [
                    'name' => 'Mitglied',
                    'value' => 50,
                ],
                [
                    'name' => 'Routinier',
                    'value' => 75,
                ],
                [
                    'name' => 'Auskenner',
                    'value' => 125,
                ],
                [
                    'name' => 'Experte',
                    'value' => 250,
                ],
                [
                    'name' => 'Profi',
                    'value' => 375,
                ],
                [
                    'name' => 'Idol',
                    'value' => 500,
                ],
                [
                    'name' => 'Mogul',
                    'value' => 750,
                ],
                [
                    'name' => 'Champ',
                    'value' => 1000,
                ],
                [
                    'name' => 'Meister',
                    'value' => 1250,
                ],
                [
                    'name' => 'Großmeister',
                    'value' => 1500,
                ],
                [
                    'name' => 'Veteran',
                    'value' => 2000,
                ],
                [
                    'name' => 'Halbgott',
                    'value' => 4000,
                ],
                [
                    'name' => 'Legende',
                    'value' => 8000,
                ],
                [
                    'name' => 'Ehrenmitglied',
                    'value' => 16000,
                ],
            ];
            $ranks = \array_reverse($ranks);

            foreach ($ranks as $index => $value) {
                if ($points >= $value['value']) {
                    /*
                     * Serach Next Rank
                     */
                    if (isset($ranks[$index - 1])) {
                        $nextPoints = $ranks[$index - 1]['value'];
                    } else {
                        $nextPoints = 999999999999999;
                    }
                    $cached = [
                        'name' => __($value['name'], 'User', 'Rank_' . $value['value']),
                        'currentPoints' => $points,
                        'currentRank' => \count($ranks) - $index,
                        'neededPoints' => $nextPoints - $points,
                        'neededPointsPercent' => \floor(($points / $nextPoints) * 100),
                        'nextRankPoints' => $nextPoints,
                        'nextRank' => \count($ranks) - $index + 1,
                        'nextRankName' => __($ranks[$index - 1]['name'], 'User', 'Rank_' . $ranks[$index - 1]['value']),
                    ];
                    $this->container->get('cache')->set($cackeKey, $cached);
                    break;
                }
            }
        }

        if (!is_array($cached)) {
            return $this->getUserRank(16000);
        }

        return $cached;
    }

    /**
     * @param $userID
     * @param $fromUser
     * @param $messageName
     * @param $message
     * @param array $params
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function createNotification($userID, $fromUser, $messageName, $message, $params = [])
    {
        if ($userID != $this->container->get('session')->getUserID()) {
            $language = $this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT Language FROM users WHERE id = ?', [$userID]);
            $message = __($message, 'Notification', $messageName, $language);
        } else {
            $message = __($message, 'Notification', $messageName);
        }

        if (!empty($params)) {
            foreach ($params as $key => $val) {
                $message = \str_replace('%' . $key . '%', $val, $message);
            }
        }

        $this->container->get('doctrine.dbal.default_connection')->insert('users_notification', [
            'userID' => $userID,
            'fromUser' => $fromUser,
            'message' => $message,
            'date' => \date('Y-m-d H:i:s'),
        ]);
    }
}
