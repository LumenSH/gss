<?php

namespace GSS\Component\Language;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class Language.
 *
 * @author Soner Sayakci <***REMOVED***>
 */
class Language
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var array
     */
    private $lanuageConfig;

    /**
     * Language constructor.
     *
     * @param SessionInterface $session
     * @param array            $lanuageConfig
     */
    public function __construct(
        SessionInterface $session,
        array $lanuageConfig
    ) {
        $this->session = $session;
        $this->lanuageConfig = $lanuageConfig;
    }

    /**
     * Returns the language of the current user.
     *
     * @return string
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getLanguage(): string
    {
        $countryCode = $this->session->get('countryCode');

        if (empty($countryCode)) {
            $this->session->set('countryCode', $this->getCountryCode());

            return $this->session->get('countryCode');
        }

        return $countryCode;
    }

    /**
     * @param string $code
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function setLanguage(string $code)
    {
        $this->session->set('countryCode', $code);
    }

    /**
     * Returns the country code.
     *
     * @return string
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getCountryCode(): string
    {
        $code = $this->session->get('countryCode');
        if (empty($code)) {
            if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                $code = $this->lanuageConfig['defaultLanguage'];
            } else {
                $code = \strtolower(\substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
            }
        }

        return isset($this->lanuageConfig['browserMapping'][$code]) ? $this->lanuageConfig['browserMapping'][$code] : $this->lanuageConfig['defaultLanguage'];
    }

    /**
     * Set php locale.
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function setLocale()
    {
        \setlocale(LC_ALL, ($this->getCountryCode() == 'de') ? 'de_DE' : 'en_US');
    }
}
