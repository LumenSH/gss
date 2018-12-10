<?php

namespace GSS\Controller\Backend;

use GSS\Component\HttpKernel\Controller;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends Controller
{
    /**
     * @Route("/backend")
     * @Route("/backend/")
     */
    public function indexAction()
    {
        $this->data['pageTitle'] = 'Backend';
        $this->data['loggedIn'] = true;
        $this->data['currentModule'] = 'Backend';

        return $this->render('backend/index/index.twig', $this->data);
    }
}
