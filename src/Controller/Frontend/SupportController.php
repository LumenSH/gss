<?php

namespace GSS\Controller\Frontend;

use GSS\Component\Form\Forms\SupportTicketType;
use GSS\Component\HttpKernel\Controller;
use GSS\Component\User\Support;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class Support.
 */
class SupportController extends Controller
{
    /** @var $support \GSS\Component\User\Support */
    private $support;

    /**
     * Support constructor.
     */
    public function init()
    {
        $this->support = $this->container->get(Support::class);
    }

    /**
     * @Route("/support")
     * @Route("/support/")
     * @Route("/support/index/{folder}")
     * Index Action.
     *
     * @param int $folder
     *
     * @return string
     */
    public function indexAction($folder = 0)
    {
        $this->View()->setPageTitle('Support');

        $this->data = $this->support->getTickets($this->userID, $folder);
        $this->data['folder'] = $folder;

        $this->data['breadcrumb'] = [
            [
                'name' => 'Support',
                'link' => $this->generateUrl('gss_frontend_support_index'),
            ],
            [
                'name' => ($folder == 0 ? __('Offene Tickets', 'Support', 'OpenTickets') : __('Geschlossene Tickets', 'Support', 'ClosedTickets')),
            ],
        ];

        return $this->render('frontend/support/index.twig', $this->data);
    }

    /**
     * @Route("/support/new")
     * New Action.
     */
    public function newAction()
    {
        $this->View()->setPageTitle('Support', 'Neues Ticket anlegen');

        $this->data['gameserver'] = $this->container->get('app.user.user')->getGameserver($this->userID);

        $bugCreateType = $this->container->get('form.factory')->create(SupportTicketType::class, $this->Request()->query->all());
        $bugCreateType->handleRequest($this->Request());

        if ($bugCreateType->isSubmitted() && $bugCreateType->isValid()) {
            $data = $bugCreateType->getData();

            /**
             * Notify admins with push notifications.
             */
            $userIds = $this->container->get('session')->Acl()->getUsersByPermission('admin_support');

            $ticketId = $this->support->createTicket($data, $this->getUser()->getId());

            $this->container->get('push.manager')->sendMessage(
                $userIds,
                'Neues Support Ticket',
                'Ticket Name: ' . $data['name'] . ', von: ' . $this->container->get('session')->getUserData('Username'),
                $this->container->getParameter('url') . 'backend#!/support/' . $ticketId
            );

            return $this->redirectToRoute('gss_frontend_support_view', ['ticketID' => $ticketId]);
        }

        $this->data['breadcrumb'] = [
            [
                'name' => 'Support',
                'link' => $this->generateUrl('gss_frontend_support_index'),
            ],
            [
                'name' => __('Neues Ticket', 'Support', 'NewTicket'),
            ],
        ];

        return $this->render('frontend/support/new_ticket.twig', [
            'form' => $bugCreateType->createView(),
        ] + $this->data);
    }

    /**
     * @Route("/support/close/{ticketID}")
     * Close Action.
     *
     * @param null $ticketID
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function closeAction($ticketID = null)
    {
        if (empty($ticketID)) {
            return $this->redirectToRoute('gss_frontend_support_index');
        }
        $this->container->get('doctrine.dbal.default_connection')->update(
                'tickets',
                [
                'folder' => 1,
            ],
                [
                    'id' => $ticketID,
                    'userID' => $this->getUser()->getId(),
                ]
            );
        $this->container->get('session')->flashMessenger()->addSuccess('Support', __('Ticket wurde geschlossen', 'Support', 'TicketClosed'));

        return $this->redirectToRoute('gss_frontend_support_index');
    }

    /**
     * @Route("/support/{ticketID}")
     * View Action.
     *
     * @param $ticketID
     *
     * @return string
     */
    public function viewAction($ticketID)
    {
        $ticketData = $this->support->getTicket($ticketID, $this->getUser()->getId());

        if ($ticketData) {
            $answer = $this->container->get('request')->getPost('answer');

            /*
             * If answer is posted
             */
            if (!empty($answer)) {
                /**
                 * Notify admins with push notifications.
                 */
                $userIds = $this->container->get('session')->Acl()->getUsersByPermission('admin_support');

                $this->container->get('push.manager')->sendMessage(
                    $userIds,
                    'Neue Antwort Support Ticket',
                    'Ticket Name: ' . $ticketData['name'] . ', von: ' . $this->container->get('session')->getUserData('Username'),
                    $this->container->getParameter('url') . 'backend#!/support/' . $ticketID
                );

                $this->support->answerTicket($ticketID, $this->userID, $answer);

                return $this->reload();
            }

            $this->data['breadcrumb'] = [
                [
                    'name' => 'Support',
                    'link' => $this->generateUrl('gss_frontend_support_index'),
                ],
                [
                    'name' => 'Ticket ' . $ticketData['name'],
                ],
            ];

            $this->View()->setPageTitle('Ticket');
            $this->data['pageTitle'] = 'Ticket ' . $ticketData['name'];
            $this->data['ticket'] = $ticketData;

            return $this->render('frontend/support/display_ticket.twig', $this->data);
        }

        return $this->redirectToRoute('gss_frontend_support_index');
    }
}
