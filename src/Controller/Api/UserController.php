<?php

namespace GSS\Controller\Api;

use GSS\Component\HttpKernel\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController
 *
 * @Route("/api")
 */
class UserController extends Controller
{
    /**
     * @Route(path="/me", methods={"GET"})
     */
    public function me()
    {
        return new JsonResponse($this->getUser());
    }
}
