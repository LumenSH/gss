<?php

namespace GSS\Controller\Api;

use GSS\Component\Api\RateLimiting;
use GSS\Component\Exception\Hosting\DaemonException;
use GSS\Component\Hosting\Gameserver\Gameserver;
use GSS\Component\HttpKernel\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ServerController
 *
 * @Route("/api")
 */
class ServerController extends Controller
{
    /**
     * @Route(path="/server", methods={"GET"})
     */
    public function listServers(): JsonResponse
    {
        return new JsonResponse($this->container->get('app.user.user')->getGameserverData($this->userID));
    }

    /**
     * @Route(path="/server/{id}", methods={"GET"})
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function getOne(int $id): JsonResponse
    {
        $gs = Gameserver::createServer($this->container, $id);

        $response = [
            'server' => $gs->getData(),
            'versions' => $gs->getVersions(),
            'currentVersion' => $gs->getCurrentVersion(),
        ];

        $response['server']['duration'] = new \DateTime(\date('Y-m-d H:i:s', $response['server']['duration']));
        $response['online'] = $response['server']['bannerOn'];

        return new JsonResponse($this->clearOutput($response));
    }

    /**
     * @Route(path="/server/{id}/start", methods={"GET"})
     *
     * @param int $id
     *
     * @throws DaemonException
     *
     * @return JsonResponse
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function start(int $id): JsonResponse
    {
        if ($this->container->get(RateLimiting::class)->isRateLimited($id)) {
            return new JsonResponse(['success' => false, 'message' => 'Server starting has been ratelimited']);
        }

        $gs = Gameserver::createServer($this->container, $id);

        return new JsonResponse([
            'success' => $gs->getDaemon()->startServer($id),
        ]);
    }

    /**
     * @Route(path="/server/{id}/stop", methods={"GET"})
     *
     * @param int $id
     *
     * @throws DaemonException
     *
     * @return JsonResponse
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function stop(int $id): JsonResponse
    {
        $gs = Gameserver::createServer($this->container, $id);

        return new JsonResponse([
            'success' => $gs->stop(),
        ]);
    }

    /**
     * @Route(path="/server/{id}/token", methods={"GET"})
     * @param int $id
     * @return JsonResponse
     * @throws DaemonException
     */
    public function token(int $id) : JsonResponse
    {
        $gs = Gameserver::createServer($this->container, $id);

        return new JsonResponse([
            'socketEndpoint' => $gs->getDaemon()->getSocketUrl(),
            'token' => $gs->getDaemon()->getToken($id)
        ]);
    }

    /**
     * @author Soner Sayakci <shyim@posteo.de>
     *
     * @param array $data
     *
     * @return array
     */
    private function clearOutput(array $data): array
    {
        \array_walk_recursive($data, function (&$item, $key) {
            if (\stripos($key, 'ssh') === 0) {
                $item = null;
            }
        });

        return $data;
    }
}
