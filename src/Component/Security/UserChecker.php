<?php

namespace GSS\Component\Security;

use Doctrine\DBAL\Connection;
use GSS\Component\Exception\Security\AccountLockedException;
use GSS\Component\Exception\Security\AccountNotActivatedException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UserChecker
 *
 * @author Soner Sayakci <shyim@posteo.de>
 */
class UserChecker implements UserCheckerInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * UserChecker constructor.
     *
     * @param Connection $connection
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Checks the user account before authentication.
     *
     * @throws AccountStatusException
     * @throws AccountLockedException
     */
    public function checkPreAuth(UserInterface $user)
    {
    }

    /**
     * Checks the user account after authentication.
     *
     * @throws AccountStatusException
     */
    public function checkPostAuth(UserInterface $user)
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->isLocked()) {
            throw new AccountLockedException($user->getInhibition());
        }

        $this->isAccountActivated($user);
    }

    /**
     * @param User $user
     *
     * @author Soner Sayakci <shyim@posteo.de>
     *
     * @throws \GSS\Component\Exception\Security\AccountNotActivatedException
     */
    private function isAccountActivated(User $user): void
    {
        if ($this->connection->fetchColumn('SELECT 1 FROM blocked_tasks WHERE Method = "activation" AND Email = ?', [
            $user->getEmail(),
        ])) {
            throw new AccountNotActivatedException(__('Bitte aktiviere dein Benutzerkonto vorher', 'Login', 'UserNotActivated'));
        }
    }
}
