<?php

namespace GSS\Component\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Extension;

class ExtensionFilter extends Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $userCache = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('timeDiff', [$this, 'getTimeDiff'], [
                'is_safe' => ['html'],
            ]),
            new \Twig_SimpleFilter('getBetween', [$this, 'getBetween'], [
                'is_safe' => ['html'],
            ]),
            new \Twig_SimpleFilter('ucfirst', [$this, 'ucfirst']),
            new \Twig_SimpleFilter('highlightUser', [$this, 'highlightUser'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    /**
     * Get Between.
     *
     * @param $last
     *
     * @return number
     */
    public function getBetween($last)
    {
        $now = \time();

        return \abs(\floor(($now - $last) / (60 * 60 * 24)));
    }

    public function getTimeDiff($interval, $granularity = 2)
    {
        if (!\is_numeric($interval)) {
            $interval = \strtotime($interval);
        }

        $interval = \time() - $interval;
        $code = $this->container->get('language')->getCountryCode();

        if ($code == 'en') {
            $units = ['1 year|@count years' => 31536000, '1 week|@count weeks' => 604800, '1 day|@count days' => 86400, '1 hour|@count hours' => 3600, '1 min|@count min' => 60, '1 sec|@count sec' => 1];
        } else {
            $units = ['1 Jahr|@count Jahre' => 31536000, '1 Woche|@count Wochen' => 604800, '1 Tag|@count Tagen' => 86400, '1 Stunde|@count Stunden' => 3600, '1 Minute|@count Minuten' => 60, '1 Sekunde|@count Sekunden' => 1];
        }
        $output = '';
        foreach ($units as $key => $value) {
            $key = \explode('|', $key);
            if ($interval >= $value) {
                $floor = \floor($interval / $value);
                $output .= ($output ? ' ' : '') . ($floor == 1 ? $key[0] : \str_replace('@count', $floor, $key[1]));
                $interval %= $value;
                --$granularity;
            }

            if ($granularity == 0) {
                break;
            }
        }

        return $output ? ($code == 'en' ? 'before' : 'Vor') . ' ' . $output : '0 sec';
    }

    public function highlightUser($code)
    {
        \preg_match_all('/@[a-zA-Z]+:/', $code, $matches);

        foreach ($matches as $match) {
            if (!isset($match[0])) {
                continue;
            }

            $match = $match[0];
            $name = \substr(\substr($match, 1), 0, -1);

            if (!isset($this->userCache[$name])) {
                $this->userCache[$name] = $this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT COUNT(*) FROM users WHERE Username LIKE ?', [$name]);
            }

            if ($this->userCache[$name] == 1) {
                $code = \str_replace($match, '<a href="' . $this->container->getParameter('url') . 'user/' . \strtolower($name) . '" class="txt-red">' . $match . '</a>', $code);
            }
        }

        return $code;
    }

    public function ucfirst($text)
    {
        return \ucfirst($text);
    }
}
