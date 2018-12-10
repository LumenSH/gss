<?php

/**
 * Shortcut for Translations.
 *
 * @param $str
 * @param string                    $textDomain
 * @param string                    $name
 * @param string|null               $code
 *
 * @return string
 */
function __($str, $textDomain = 'root', $name = '', $code = null)
{
    global $kernel;

    return $kernel->getContainer()->get('translation')->getString($name, $str, $textDomain, $code);
}
