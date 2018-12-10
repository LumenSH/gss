<?php

namespace GSS\Controller\Frontend;

use GSS\Component\HttpKernel\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Error.
 */
class ErrorController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \InvalidArgumentException
     */
    public function error_404Action()
    {
        $this->View()->setPageTitle('404');

        $response = $this->render('frontend/error/404.twig');
        $response->setStatusCode(Response::HTTP_NOT_FOUND);

        return $response;
    }

    /**
     * @param \Exception $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \InvalidArgumentException
     */
    public function error_500Action($e = null)
    {
        $this->View()->setPageTitle('Internal Error');

        $this->container->get('logger')->critical($e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        $response = $this->render('frontend/error/500.twig');
        $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);

        return $response;
    }
}
