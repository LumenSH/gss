<?php

namespace GSS\Controller\Backend;

use GSS\Component\Hosting\Gameserver\Gameserver;
use Swift_Message;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class User.
 */
class UserController extends Backend
{
    /**
     * @Route("/backend/user")
     * @Route("/backend/user/")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return parent::indexAction();
    }

    /**
     * @Route("/backend/user/getGPHistory")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getGPHistoryAction()
    {
        $userID = $this->Request()->get('userID');

        $limit = $this->Request()->get('limit', 25);
        $page = $this->Request()->get('page', 1);
        $search = $this->Request()->get('search', []);
        $sorting = [
            'key' => 'ID',
            'order' => 'DESC',
        ];
        $filter = [
            'userID' => $userID,
        ];

        return new JsonResponse($this->listQuery('gp_stats', $limit, $page, $sorting, $filter, $search));
    }

    /**
     * @Route("/backend/user/servers")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getServersAction()
    {
        $userID = $this->Request()->get('userID');

        $data['data'] = $this->container->get('doctrine.dbal.default_connection')->fetchAll('
        SELECT
          gameserver.id,
          gameserver.Port,
          gameroot_ip.IP,
          products.internalName as Game,
          users_to_gameserver.Rights,
          CONCAT("view/", gameserver.id) as Url
        FROM
          users_to_gameserver
        INNER JOIN gameserver ON(gameserver.id = users_to_gameserver.gameserverID)
        INNER JOIN gameroot_ip ON(gameroot_ip.id = gameserver.gamerootIpID)
        INNER JOIN products ON(products.id = gameserver.productID)
        WHERE users_to_gameserver.userID = ?
        ', [
            $userID,
        ]);

        return new JsonResponse($data);
    }

    /**
     * @Route("/backend/user/single")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function singleAction()
    {
        return $this->render('backend/user/single.twig');
    }

    /**
     * @Route("/backend/user/changeUser/{id}")
     *
     * @param null $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changeUserAction($id = null)
    {
        if ($this->container->get('session')->Acl()->isAllowed('admin_user_change')) {
            $email = $this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT Email FROM users WHERE id = ?', [$id]);

            $this->container->get('app.user.user')->loginWithEmail($this->Request(), $email);

            return $this->redirectToRoute('index');
        }

        throw new NotFoundHttpException();
    }

    /**
     * @Route("/backend/user/sendMessage")
     *
     * @return JsonResponse
     */
    public function sendMessageAction()
    {
        $data = $this->Request()->getAjaxPost();

        $userData = $this->container->get('doctrine.dbal.default_connection')->fetchAssoc('SELECT * FROM users WHERE id = ?', [
            $data['userId'],
        ]);

        $mail = new Swift_Message();
        $mail
            ->setFrom($this->container->getParameter('email.sender'), $this->container->getParameter('email.sendername'))
            ->setTo($userData['Email'], $userData['Username'])
            ->setSubject($data['subject'])
            ->setBody($data['message'], 'text/html', 'UTF-8');

        $this->container->get('mailer')->send($mail);

        return new JsonResponse();
    }

    /**
     * @param int    $limit
     * @param int    $page
     * @param string $sorting
     * @param array  $filter
     * @param array  $search
     *
     * @return array
     */
    protected function getList($limit = 25, $page = 1, $sorting = '', $filter = [], $search = [])
    {
        return $this->listQuery('users', $limit, $page, $sorting, $filter, $search);
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    protected function getOne($id)
    {
        return $this->container->get('doctrine.dbal.default_connection')->fetchAssoc('SELECT * FROM users WHERE id = ?', [$id]);
    }

    protected function save($id, $data)
    {
        if (!empty($data['Inhibition'])) {
            $gameserver = $this->container->get('doctrine.dbal.default_connection')->fetchAll('SELECT id FROM gameserver WHERE userID = ?', [$data['id']]);
            foreach ($gameserver as $gsLine) {
                $gs = Gameserver::createServer($this->container, $gsLine['id']);
                $gs->stop();
            }
        }

        if (!empty($data['Password'])) {
            $encryptedData = $this->container->get('app.security.password_encoder.bcrypt')->crypt($data['Password']);
            $data['Password'] = $encryptedData['password'];
            $data['Salt'] = $encryptedData['salt'];
        } else {
            if (isset($data['Salt'])) {
                unset($data['Salt']);
            }
            unset($data['Password']);
        }

        try {
            $this->container->get('doctrine.dbal.default_connection')->update('users', $data, ['id' => $id]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }

        return new JsonResponse();
    }
}
