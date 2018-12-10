<?php

namespace GSS\Controller\Frontend;

use GSS\Component\Content\Cms;
use GSS\Component\HttpKernel\Controller;
use GSS\Component\Language\Language;
use GSS\Models\Cms\CmsService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Cms.
 */
class CmsController extends Controller
{
    /**
     * Display CMS Page.
     *
     * @param $slug
     * @param CmsService $cmsService
     * @param Language   $language
     *
     * @return string
     */
    public function detailAction($slug, CmsService $cmsService, Language $language)
    {
        $rewrite = $this->container->get('rewrite_manager')->getRewriteParamsByUrl($slug);

        if (isset($rewrite['forwardParams'])) {
            $cmsID = \json_decode($rewrite['forwardParams'], true)['cmsID'];
        } else {
            throw new NotFoundHttpException();
        }

        $page = $cmsService->find($cmsID);

        $this->data = [
            'title' => $page->getTitle()[$language->getCountryCode()] ?? '',
            'content' => $page->getContent()[$language->getCountryCode()] ?? '',
            'meta' => $page->getMeta()[$language->getCountryCode()] ?? '',
            'record' => [
                'title' => $page->getTitle()[$language->getCountryCode()] ?? '',
                'content' => $page->getContent()[$language->getCountryCode()] ?? '',
                'meta' => $page->getMeta()[$language->getCountryCode()] ?? '',
            ],
        ];

        $this->data['breadcrumb'] = [
            [
                'name' => $page->getTitle()[$language->getCountryCode()] ?? '',
                'link' => '#',
            ],
        ];

        return $this->render('frontend/cms/detail.twig', $this->data);
    }
}
