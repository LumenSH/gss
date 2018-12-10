<?php

namespace GSS\Controller\Backend;

use GSS\Component\Util;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SupportController extends Backend
{
    /**
     * @Route("/backend/support/")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return parent::indexAction();
    }

    /**
     * @Route("/backend/support/getOptions/")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getOptionsAction()
    {
        $typesConfig = (array) $this->container->getParameter('support');
        $gamesFetch = $this->container->get('doctrine.dbal.default_connection')->fetchAll('SELECT id,name FROM products');
        $types = [
            [
                'id' => -1,
                'name' => 'Alle',
            ],
        ];
        $games = [
            [
                'id' => -1,
                'name' => 'Alle',
            ],
        ];

        foreach ($typesConfig as $key => $item) {
            $types[] = [
                'id' => $key,
                'name' => $item,
            ];
        }

        foreach ($gamesFetch as $game) {
            $games[] = [
                'id' => $game['id'],
                'name' => $game['name'],
            ];
        }

        return new JsonResponse([
            'types' => $types,
            'games' => $games,
        ]);
    }

    public function getOne($ticketID)
    {
        $typesConfig = $this->container->getParameter('support');

        $this->data['ticket'] = $this->container->get('doctrine.dbal.default_connection')->fetchAssoc('SELECT * FROM tickets WHERE id = ?', [$ticketID]);
        $this->data['ticketAnswer'] = $this->container->get('doctrine.dbal.default_connection')
            ->createQueryBuilder()
            ->from('tickets_answers', 'tickets_answers')
            ->addSelect('tickets_answers.*')
            ->addSelect('users.Username as username')
            ->leftJoin('tickets_answers', 'users', 'users', 'users.id = tickets_answers.userID')
            ->where('ticketID = :id')
            ->setParameter('id', $ticketID)
            ->orderBy('tickets_answers.id', 'ASC')
            ->execute()
            ->fetchAll();

        if (!empty($this->data['ticket']['gameserverID'])) {
            $this->data['ticket']['gsUrl'] = $this->container->getParameter('url') . 'server/view/' . $this->data['ticket']['gameserverID'];
        } else {
            $this->data['ticket']['gsUrl'] = '';
        }

        $this->data['ticket']['typ'] = $typesConfig[$this->data['ticket']['typ']];

        return $this->data;
    }

    /**
     * @Route("/backend/support/answerTicket/")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function answerTicketAction()
    {
        $this->container->get('doctrine.dbal.default_connection')->update('tickets', [
            'lastchange_at' => \time(),
        ], ['id' => $this->Request()->getPost('ticketID')]);

        $this->container->get('doctrine.dbal.default_connection')->insert('tickets_answers', [
            'ticketID' => $this->Request()->getPost('ticketID'),
            'userID' => $this->userID,
            'message' => $this->Request()->getPostHtml('answer'),
            'date' => \time(),
            'support' => 1,
        ]);

        $ticketData = $this->container->get('doctrine.dbal.default_connection')->fetchAssoc('SELECT name, userID FROM tickets WHERE id = ?', [
            $this->Request()->getPost('ticketID'),
        ]);

        $userInfo = $this->container->get('doctrine.dbal.default_connection')->fetchAssoc('SELECT Username, Language, Email FROM users WHERE id = ?', [
            $ticketData['userID'],
        ]);

        $this->container->get('translation')->setLanguage($userInfo['Language']);

        $mailTemplate = $this->renderView('email/ticket.twig', [
            'name' => $ticketData['name'],
            'message' => $this->Request()->getPostHtml('answer'),
            'link' => $this->container->get('rewrite_manager')->getRewriteByParams(['ticketID' => $this->Request()->getPost('ticketID')])['link'],
        ]);

        $mail = new \Swift_Message();
        $mail
            ->setFrom($this->container->getParameter('email.sender'), $this->container->getParameter('email.sendername'))
            ->setTo($userInfo['Email'], $userInfo['Username'])
            ->setSubject(__('Neue Antwort auf Ihr Ticket', 'Mail', 'NewAnswerTicket', $userInfo['Language']))
            ->setBody($mailTemplate, 'text/html', 'UTF-8');

        $this->container->get('mailer')->send($mail);

        $this->container->get('push.manager')->sendMessage(
            $ticketData['userID'],
            __('Neue Antwort auf dein Ticket', 'Notification', 'TicketHeader'),
            \str_replace('%name%', $ticketData['name'], __('Auf dein Ticket bei Gameserver-Sponsor mit den Namen %name% wurde geantwortet', 'Notification', 'TicketText'))
        );

        return new JsonResponse();
    }

    /**
     * @Route("/backend/support/closeTicket/{ticketID}")
     *
     * @param $ticketID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function closeTicketAction($ticketID)
    {
        $this->container->get('doctrine.dbal.default_connection')->update('tickets', [
            'folder' => 1,
            'lastchange_at' => \time(),
        ], ['id' => $ticketID]);

        $this->container->get('doctrine.dbal.default_connection')->insert('tickets_answers', [
            'ticketID' => $ticketID,
            'userID' => $this->userID,
            'message' => __('Ticket wurde geschlossen', 'Support', 'TicketClose'),
            'date' => \time(),
            'support' => 1,
        ]);

        return new JsonResponse();
    }

    protected function getList($limit = 25, $page = 1, $sorting = '', $filter = [], $search = [])
    {
        $qb = $this->container->get('doctrine.dbal.default_connection')->createQueryBuilder();

        $sql = $qb
            ->from('tickets', 'tickets')
            ->addSelect('tickets.*, users.Username, products.name as Game')
            ->addSelect('(SELECT Username FROM users WHERE id = (SELECT userID FROM tickets_answers WHERE ticketID = tickets.id ORDER BY id DESC LIMIT 1)) as lastAnswer')
            ->addSelect('(SELECT Role FROM users WHERE id = (SELECT userID FROM tickets_answers WHERE ticketID = tickets.id ORDER BY id DESC LIMIT 1)) as lastAnswerRole')
            ->leftJoin('tickets', 'users', 'users', 'users.id = tickets.userID')
            ->leftJoin('tickets', 'gameserver', 'gameserver', 'gameserver.id = tickets.gameserverID')
            ->leftJoin('tickets', 'products', 'products', 'products.id = gameserver.productID');

        if (isset($search['folder']) && $search['folder'] != -1) {
            $sql
                ->setParameter('folder', $search['folder'])
                ->andWhere('tickets.folder = :folder');
        }

        if (isset($search['type']) && -1 != $search['type']['id']) {
            $sql
                ->setParameter('typeId', $search['type']['id'])
                ->andWhere('tickets.typ = :typeId');
        }

        if (isset($search['game']) && -1 != $search['game']['id']) {
            $sql
                ->setParameter('gameId', $search['game']['id'])
                ->andWhere('products.id = :gameId');
        }

        if (isset($search['id'])) {
            $sql
                ->setParameter('supportId', $search['id'])
                ->andWhere('tickets.id = :supportId');
        }

        if (isset($search['search'])) {
            $sql
                ->setParameter('search', '%' . $search['search'] . '%')
                ->andWhere('tickets.name LIKE :search OR (SELECT COUNT(*) FROM tickets_answers WHERE tickets_answers.ticketID = tickets.id AND tickets_answers.message LIKE :search) > 0');
        }

        $countSQL = clone $sql;
        $count = (int) $countSQL
            ->select('COUNT(*) as count')
            ->execute()
            ->fetchColumn();

        $sql
            ->setMaxResults($limit)
            ->addOrderBy('tickets.lastchange_at', 'DESC')
            ->setFirstResult(Util::getSqlOffset($page, $limit));

        $data = $sql->execute()->fetchAll();
        $pageination = [];

        for ($i = 1; $i <= \ceil($count / $limit); ++$i) {
            $pageination[] = $i;
        }

        return [
            'data' => $data,
            'totalCount' => $count,
            'pages' => \ceil($count / $limit),
            'pageination' => $pageination,
        ];
    }
}
