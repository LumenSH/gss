<?php

namespace GSS\Component\Exception\Security;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

/**
 * Class AccountLockedException
 *
 * @author Soner Sayakci <shyim@posteo.de>
 */
class AccountLockedException extends AccountStatusException
{
}
