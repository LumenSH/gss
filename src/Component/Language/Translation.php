<?php

namespace GSS\Component\Language;

use Doctrine\DBAL\Connection;
use GSS\Component\Traits\CacheExtension;
use Symfony\Component\Cache\Adapter\AdapterInterface;

/**
 * Class Translation.
 *
 * @author Soner Sayakci <***REMOVED***>
 */
class Translation
{
    use CacheExtension;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var AdapterInterface
     */
    private $cache;

    /**
     * @var array
     */
    private $language_items = [];

    /**
     * @var string
     */
    private $code;

    /**
     * @var array
     */
    private $languageConfig;

    /**
     * Translation constructor.
     *
     * @param AdapterInterface $cache
     * @param Connection       $connection
     * @param Language         $language
     * @param array            $languageConfig
     */
    public function __construct(
        AdapterInterface $cache,
        Connection $connection,
        Language $language,
        array $languageConfig
    ) {
        $this->cache = $cache;
        $this->connection = $connection;
        $this->code = $language->getLanguage();
        $this->languageConfig = $languageConfig;

        $this->setTranslationCache($this->code);
    }

    /**
     * Change language of translation.
     *
     * @param string $code
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function setLanguage($code)
    {
        if (empty($this->language_items[$code])) {
            $this->setTranslationCache($code);
        }
    }

    /**
     * @param string $code
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function setTranslationCache($code)
    {
        $this->language_items[$code] = $this->getLanguageCache($code);
    }

    /**
     * @author Soner Sayakci <***REMOVED***>
     */
    public function generateTranslationCaches()
    {
        $data = $this->connection->fetchAll('SELECT * FROM translation');
        $writeData = [];

        foreach ($this->languageConfig['browserMapping'] as $languageKey) {
            $cache = [];

            foreach ($data as $line) {
                $cache[$line['namespace']][$line['name']] = $line[$languageKey];
            }

            $writeData['language_' . $languageKey] = $cache;
        }

        $this->setMultipleData($this->cache, $writeData);
    }

    /**
     * @param string $name
     * @param string $defaultValue
     * @param string $namespace
     * @param null   $code
     *
     * @return string
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getString($name = '', $defaultValue = '', $namespace = 'root', $code = null)
    {
        $code = ($code == null) ? $this->code : $code;

        if (empty($this->language_items[$code])) {
            $this->setTranslationCache($code);
        }

        if (isset($this->language_items[$code][$namespace][$name])) {
            return $this->language_items[$code][$namespace][$name];
        }

        $this->addTranslation($name, $defaultValue, $namespace, $code);

        return $defaultValue;
    }

    /**
     * @param string $name
     * @param string $value
     * @param string $namespace
     * @param string $code
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private function addTranslation($name, $value, $namespace, $code)
    {
        if (empty($this->language_items[$code][$namespace])) {
            $this->language_items[$code][$namespace] = [];
        }
        $this->language_items[$code][$namespace][$name] = $value;

        $basicQuery = [
            'name' => $name,
            'namespace' => $namespace,
        ];

        $this->languageConfig['browserMapping'][] = 'en';

        foreach ($this->languageConfig['browserMapping'] as $language) {
            $basicQuery[$language] = $value;
        }

        $this->connection->insert('translation', $basicQuery);

        $this->generateTranslationCaches();
    }

    /**
     * @param string $language
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private function getLanguageCache($language)
    {
        $cacheItem = $this->cache->getItem('language_' . $language);

        if (!$cacheItem->isHit()) {
            $cache = [];
            $translations = $this->connection->fetchAll('SELECT name, namespace,' . $language . ' FROM translation');

            foreach ($translations as $trans) {
                if (!isset($cache[$trans['namespace']])) {
                    $cache[$trans['namespace']] = [];
                }
                $cache[$trans['namespace']][$trans['name']] = $trans[$language];
            }

            $cacheItem->set($cache);
            $this->cache->save($cacheItem);
        }

        return $cacheItem->get();
    }
}
