<?php

namespace GSS\Controller\Backend;

use GSS\Models\Cms\Cms;
use GSS\Models\Cms\CmsService;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class Cms.
 */
class CmsController extends Backend
{
    /**
     * @var CmsService
     */
    private $cmsService;

    /**
     * CmsController constructor.
     *
     * @param CmsService $cmsService
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function __construct(CmsService $cmsService)
    {
        $this->cmsService = $cmsService;
    }

    /**
     * @Route("/backend/cms")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return parent::indexAction();
    }

    /**
     * @param int    $limit
     * @param int    $page
     * @param string $sorting
     * @param array  $filter
     * @param array  $search
     *
     * @return array
     */
    protected function getList($limit = 25, $page = 1, $sorting = '', $filter = [], $search = [])
    {
        $data = $this->listQuery('cms', $limit, $page, $sorting, $filter, $search);

        foreach ($data['data'] as &$row) {
            $row['content'] = \json_decode($row['content'], true);
            $row['meta'] = \json_decode($row['meta'], true);
            $row['title'] = \json_decode($row['title'], true);
        }

        return $data;
    }

    /**
     * @param $id
     *
     * @return Cms
     */
    protected function getOne($id)
    {
        return $this->cmsService->find($id);
    }

    /**
     * @param $id
     * @param $data
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws \Psr\Cache\InvalidArgumentException
     *
     * @return Cms
     */
    protected function save($id, $data)
    {
        unset($data['id']);

        if ($id !== null) {
            $cms = $this->cmsService->find($id);
        } else {
            $cms = new Cms();
        }

        if ($cms === null) {
            $cms = new Cms();
        }

        $cms->fromArray($data);

        if ($cms->getId() !== null) {
            return $this->cmsService->update($cms);
        }

        return $this->cmsService->create($cms);
    }

    /**
     * @param $id
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws \Psr\Cache\InvalidArgumentException
     *
     * @return Cms
     */
    protected function delete($id)
    {
        $cms = $this->cmsService->find($id);

        if ($cms !== null) {
            $this->cmsService->remove($cms);
        }

        return $cms;
    }
}
