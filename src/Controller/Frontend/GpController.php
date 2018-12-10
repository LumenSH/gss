<?php

namespace GSS\Controller\Frontend;

use GSS\Component\Commerce\GP;
use GSS\Component\HttpKernel\Controller;
use GSS\Component\Twig\ExtensionGlobals;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class Gp.
 */
class GpController extends Controller
{
    /**
     * @Route("/gp")
     *
     * @return string|\Symfony\Component\HttpFoundation\Response
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function indexAction()
    {
        $this->View()->setPageTitle('GP Übersicht');

        $this->data['GP'] = $this->container->get('session')->getUserData('GP');
        $this->data['GPStats'] = $this->container->get(GP::class)->getGPStats($this->userID);
        $this->container->set('jsData', ['gpstats' => $this->data['GPStats']['Graph']]);

        $this->data['breadcrumb'] = [
            [
                'name' => __('GP Übersicht', 'GP', 'BreadcrumbTitle'),
                'link' => '#',
            ],
        ];

        return $this->render('frontend/gp/index.twig', $this->data);
    }

    /**
     * @Route("/gp/ajax")
     *
     * @return JsonResponse
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function ajaxAction()
    {
        $offset = $this->Request()->request->get('offset');
        $typ = $this->Request()->request->get('type');

        $sqlAdd = '';
        if ($typ !== 'all') {
            $sqlAdd = ' AND status = ' . $this->container->get('doctrine.dbal.default_connection')->quote($typ);
        }

        $data = $this->container->get('doctrine.dbal.default_connection')->fetchAll('SELECT * FROM gp_stats WHERE userID = ? ' . $sqlAdd . ' ORDER BY id DESC LIMIT ' . ($offset * 10) . ', 10', [$this->userID]);

        return new JsonResponse(['data' => $data]);
    }
}
