<?php

namespace GSS\Component\Cron;

use Doctrine\DBAL\Connection;
use GSS\Component\Language\Translation;
use GSS\Component\Routing\RewriteManager;

/**
 * Class RefreshCache.
 *
 * @author Soner Sayakci <***REMOVED***>
 */
class RefreshCache implements CronInterface
{
    /**
     * @var RewriteManager
     */
    protected $rewriteManager;

    /**
     * @var Connection
     */
    protected $database;

    /**
     * @var Translation
     */
    protected $translation;

    /**
     * RefreshCache constructor.
     *
     * @param RewriteManager $rewriteManager
     * @param Translation    $translation
     */
    public function __construct(
        RewriteManager $rewriteManager,
        Translation $translation
    ) {
        $this->rewriteManager = $rewriteManager;
        $this->translation = $translation;
    }

    /**
     * @return bool
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function start(): bool
    {
        $this->rewriteManager->reloadRewriteCache();
        $this->translation->generateTranslationCaches();

        return true;
    }
}
