<?php

namespace Codeception\Module\Drupal\UserRegistry;

/**
 * Class DrupalTestUser - represents a user on the Drupal site being tested.
 *
 * @package Codeception\Module
 */
class DrupalTestUser
{
    /**
     * @var string
     *   The username of this person.
     */
    public $name;

    /**
     * @var string
     *   The password for this user's account.
     */
    public $pass;

    /**
     * The email address for this user's account.
     *
     * @var string
     */
    public $email;

    /**
     * @var string[]
     *   The roles that this user should be given.
     */
    public $roles = array();

    /**
     * Constructor.
     *
     * @param string $name
     *   The username of this person.
     * @param string $pass
     *   The password for this user's account.
     * @param string[] $roles
     *   The role that this user should be given.
     * @param string|null $email
     *   The email address that this user should be given.
     */
    public function __construct($name, $pass, $roles = array(), $email = null)
    {
        $this->name = $name;
        $this->pass = $pass;
        $this->roles = $roles;
        $this->email = $email;
    }

    /**
     * Implement __toString().
     *
     * Codeception will now print the name rather than class name.
     *
     * @return string
     *   The name.
     */
    public function __toString()
    {
        return $this->name;
    }
}
