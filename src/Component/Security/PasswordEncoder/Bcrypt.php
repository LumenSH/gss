<?php

namespace GSS\Component\Security\PasswordEncoder;

class Bcrypt
{
    public function generateSalt()
    {
        return \substr(\str_shuffle('./0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 22);
    }

    public function crypt($password, $salt = '')
    {
        if (empty($salt)) {
            $salt = $this->generateSalt();
        }

        $hashedPassword = \crypt($password, '$2a$08$' . $salt);

        return ['password' => $hashedPassword, 'salt' => $salt];
    }

    public function verify($databasePassword, $password, $salt)
    {
        $hashArray = $this->crypt($password, $salt);

        return $hashArray['password'] == $databasePassword;
    }
}
