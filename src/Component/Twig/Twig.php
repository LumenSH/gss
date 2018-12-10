<?php

namespace GSS\Component\Twig;

use Twig_Environment;

/**
 * Class Twig.
 */
class Twig extends Twig_Environment
{
    /**
     * Sets the PageTitle.
     *
     * @param $title
     * @param string $subTitle
     */
    public function setPageTitle($title, $subTitle = ''): void
    {
        global $kernel;

        $content = $kernel->getContainer()->get('translation')->getString($title, $title, 'pageTitle');

        if (!empty($subTitle)) {
            $this->addGlobal('subPageTitle', $kernel->getContainer()->get('translation')->getString($subTitle, $subTitle, 'subPageTitle'));
        }

        $this->addGlobal('pageTitle', $content);
    }
}
