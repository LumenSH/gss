<?php

namespace GSS\Component\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, \JsonSerializable
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getId()
    {
        return $this->data['id'];
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return string[] The user roles
     */
    public function getRoles()
    {
        return ['ROLE_' . \strtoupper($this->data['Role'])];
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->data['Password'];
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return $this->data['Salt'];
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->data['Username'];
    }

    /**
     * @return mixed
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function getEmail()
    {
        return $this->data['Email'];
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
    }

    /**
     * @return array
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return mixed
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function getInhibition()
    {
        return $this->data['Inhibition'];
    }

    /**
     * @return bool
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function isLocked(): bool
    {
        return !empty($this->data['Inhibition']);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @see http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *               which is a value of any type other than a resource
     *
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->data;
    }
}
