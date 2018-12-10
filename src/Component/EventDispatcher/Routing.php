<?php

namespace GSS\Component\EventDispatcher;

use GSS\Controller\Frontend\ErrorController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class Routing
 *
 * @author Soner Sayakci <shyim@posteo.de>
 */
class Routing implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Routing constructor.
     *
     * @param ContainerInterface $container
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onControllerException',
        ];
    }

    public function onControllerException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if (\strpos($event->getRequest()->getRequestUri(), '/api') === 0 || \strpos($event->getRequest()->headers->get('REFERER'), '/api') !== false) {
            $event->setResponse(new JsonResponse([
                'success' => false,
                'type' => \get_class($exception),
                'error' => $exception->getMessage(),
            ]));

            return;
        }

        $error500 = new ErrorController();
        $error500->setContainer($this->container);

        if ($exception instanceof NotFoundHttpException) {
            $event->setResponse($error500->error_404Action());
        } else {
            $event->setResponse($error500->error_500Action($exception));
        }
    }
}
