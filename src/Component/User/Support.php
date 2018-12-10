<?php

namespace GSS\Component\User;

use Doctrine\DBAL\Connection;
use GSS\Component\HttpKernel\Request;
use GSS\Component\Session\Session;

class Support
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Request
     */
    private $request;

    /**
     * Support constructor.
     *
     * @param Connection $connection
     * @param Session    $session
     * @param Request    $request
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function __construct(
        Connection $connection,
        Session $session,
        Request $request
    ) {
        $this->connection = $connection;
        $this->session = $session;
        $this->request = $request;
    }

    public function getTickets($userID = null, $folder = 0)
    {
        $returnArray = [];

        $returnArray['count'] = [
            'closed' => $this->connection->fetchColumn('SELECT COUNT(*) AS closedCount FROM tickets WHERE userID = ? AND folder = 1', [$userID]),
            'opened' => $this->connection->fetchColumn('SELECT COUNT(*) AS closedCount FROM tickets WHERE userID = ? AND folder = 0', [$userID]),
            'trash' => $this->connection->fetchColumn('SELECT COUNT(*) AS closedCount FROM tickets WHERE userID = ? AND folder = 2', [$userID]),
        ];

        $returnArray['data'] = $this->connection->fetchAll('
			SELECT
				*
			FROM
				tickets
			WHERE
				userID = ? AND
				folder = ?
			', [$userID, $folder]);

        return $returnArray;
    }

    public function createTicket($data, $userId)
    {
        if ($data['typ'] != 1) {
            unset($data['gameserver']);
        }

        $this->connection->insert('tickets', [
            'userID' => $userId,
            'gameserverID' => empty($data['gameserver']) ? null : $data['gameserver'],
            'name' => $data['name'],
            'typ' => $data['typ'],
            'folder' => 0,
            'created_at' => \time(),
            'lastchange_at' => \time(),
        ]);

        $ticketID = $this->connection->lastInsertId();

        $this->connection->insert('tickets_answers', [
            'ticketID' => $ticketID,
            'userID' => $this->session->getUserID(),
            'message' => $this->request->xss_clean($data['question']),
            'date' => \time(),
        ]);

        $this->session->flashMessenger()->addSuccess('Support', __('Dein Ticket wurde erfolgreich angelegt', 'Support', 'TicketCreated'));

        return $ticketID;
    }

    public function getTicket($ticketID, $userId)
    {
        $ticketData = $this->connection->fetchAssoc('SELECT * FROM tickets WHERE id = ? and userID = ?', [$ticketID, $userId]);

        if (empty($ticketData)) {
            return false;
        }

        $ticketData['answers'] = $this->connection->fetchAll('SELECT *, (SELECT Username FROM users WHERE ID = tickets_answers.userID) as username FROM tickets_answers WHERE ticketID = ?', [$ticketID]);

        return $ticketData;
    }

    public function answerTicket($ticketID, $userID, $answer)
    {
        $this->connection->insert('tickets_answers', [
            'ticketID' => $ticketID,
            'userID' => $userID,
            'message' => $answer,
            'date' => \time(),
        ]);
    }
}
