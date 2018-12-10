<?php

namespace GSS\Controller\Backend;

use GSS\Component\HttpKernel\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class Cache.
 */
class CacheController extends Controller
{
    /**
     * @Route("/backend/cache/")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $directoryToClean = [];
        foreach ($directoryToClean as $cleanDir) {
            $dirContent = \scandir($cleanDir, SCANDIR_SORT_NONE);

            foreach ($dirContent as $content) {
                if ($content !== '.' && $content !== '..') {
                    \unlink($cleanDir . '/' . $content);
                }
            }
        }

        $this->container->get('cache')->set('language_de', null);
        $this->container->get('cache')->set('language_en', null);
        $this->container->get('cache')->reloadRewriteCache();
        $this->container->get('translation')->generateTranslationCaches();

        return new JsonResponse();
    }
}
