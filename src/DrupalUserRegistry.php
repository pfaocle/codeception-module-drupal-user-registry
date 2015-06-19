<?php

namespace Codeception\Module;

use Codeception\Exception\Module as ModuleException;
use Codeception\Module;
use Codeception\Module\Drupal\UserRegistry\DrupalTestUser;
use Codeception\Module\Drupal\UserRegistry\DrushTestUserManager;
use Codeception\Module\Drupal\UserRegistry\TestUserManagerInterface;
use Codeception\Module\Drupal\UserRegistry\Storage\ModuleConfigStorage;
use Codeception\Module\Drupal\UserRegistry\Storage\StorageInterface;

/**
 * DrupalUserRegistry - a Codeception module for managing test users on Drupal sites.
 *
 * Configuration:
 *
 * ```
 * modules:
 *     enabled:
 *         - PhpBrowser
 *         - AcceptanceHelper
 *         - DrupalUserRegistry
 *     config:
 *         PhpBrowser:
 *             url: 'http://localhost/myapp/'
 *         DrupalUserRegistry:
 *             roles: ['administrator', 'editor', 'sub editor', 'lowly-user', 'authenticated']  # A list of user roles.
 *             emails: # A list of emails for each role. Not all roles need to have emails.
 *               - administrator: 'admin@example.com'
 *               - editor: 'editor@example.com'
 *             password: 'test123!'         # The password to use for all test users.
 *             create: true                 # Whether to create all defined test users at the start of the suite.
 *             delete: true                 # Whether to delete all defined test users at the end of the suite.
 *             drush-alias: '@mysite.local' # The Drush alias to use when managing users via DrushTestUserManager.
 *
 */
class DrupalUserRegistry extends Module
{
    /**
     * Human-readable name of this module.
     */
    const DRUPAL_USER_REGISTRY_MODULE_NAME = 'Drupal User Registry';

    /**
     * Username of the test user with $uid = 1, generally excluded from lists
     * of user roles.
     */
    const DRUPAL_ROOT_USER_USERNAME = 'root';

    /**
     * Domain to use to generate email addresses for users.
     */
    const DRUPAL_USER_EMAIL_DOMAIN = 'example.com';

    /**
     * @var array
     *   Optional configuration with default values.
     */
    protected $config = [];

    /**
     * @var array
     *   Required configuration.
     */
    protected $requiredFields = ['roles', 'password'];

    /**
     * @var TestUserManagerInterface
     *   Stories the manager used to create/delete users.
     */
    protected $testUserManager;

    /**
     * @var DrupalTestUser[]
     *   An array of People objects.
     */
    protected $drupalTestUsers = [];

    /**
     * A reference to the user who is currently logged in, if there is one.
     *
     * @var DrupalTestUser
     */
    protected $loggedInUser;

    /**
     * Initialize the module. Check for required configuration then load users.
     */
    public function _initialize()
    {
        // @todo Using other methods of storage, set via config (eg storage: 'ModuleConfigStorage').
        // @todo Using other methods of managing test users, set via config.
        $this->testUserManager = new DrushTestUserManager($this->config);
        $this->loadUsers(new ModuleConfigStorage($this->config));
    }

    /**
     * Loads configured DrupalTestUser objects into the registry.
     *
     * @param StorageInterface $storage
     *   A Storage object defining the roles and other configuration.
     */
    protected function loadUsers(StorageInterface $storage)
    {
        $this->storage = $storage;
        $this->drupalTestUsers = $storage->load();
    }

    /**
     * Get a user by name.
     *
     * @param string $name
     *   The name of the user.
     *
     * @return bool|DrupalTestUser
     *   The DrupalTestUser representing this user. Boolean false if not found.
     */
    public function getUser($name)
    {
        foreach ($this->drupalTestUsers as $drupalTestUser) {
            if ($drupalTestUser->name == $name) {
                return $drupalTestUser;
            }
        }
        return false;
    }

    /**
     * Returns a user account object for $role
     *
     * @param string $role
     *   The returned user will be in this role.
     *
     * @return DrupalTestUser
     */
    public function getUserByRole($role)
    {
        return $this->drupalTestUsers[$role];
    }

    /**
     * Get a list of roles available on the site.
     *
     * @return array
     *   List of roles expected to be available on this site.
     */
    public function getRoles()
    {
        $roles = $this->drupalTestUsers;
        if (array_key_exists(self::DRUPAL_ROOT_USER_USERNAME, $roles)) {
            unset($roles[self::DRUPAL_ROOT_USER_USERNAME]);
        }
        return array_keys($roles);
    }

    /**
     * Get the "root" user with  uid 1, if configured.
     *
     * @return DrupalTestUser
     *   The configured "root" user.
     *
     * @throws ModuleException
     */
    public function getRootUser()
    {
        if (!isset($this->config['root']['username']) || !isset($this->config['root']['password'])) {
            throw new ModuleException(
                __CLASS__,
                "Credentials for the root user (username, password) are not configured."
            );
        }
        return new DrupalTestUser($this->config['root']['username'], $this->config['root']['password']);
    }

    /**
     * Preparation done before a suite is run: create all test users set in storage, if configured to do so.
     */
    public function _beforeSuite()
    {
        $this->manageTestUsers('create');
    }

    /**
     * Clean up performed after a suite is run: delete all test users set in storage, if configured to do so.
     */
    public function _afterSuite()
    {
        $this->manageTestUsers('delete');
    }

    /**
     * Manages (creates/deletes) Drupal users set in test user storage.
     *
     * @param string $op
     *   The operation to perform, either "create" or "delete".
     *
     * @throws \Codeception\Exception\Module
     */
    private function manageTestUsers($op)
    {
        // Validate the $op parameter and set an appropriate message.
        switch ($op) {
            case "create":
                $msg = "Creating test users.";
                break;
            case "delete":
                $msg = "Deleting test users.";
                break;
            default:
                throw new \Codeception\Exception\Module(__CLASS__, "Invalid operation $op when managing users.");
        }

        // Only act if we're configured to to do.
        if (isset($this->config[$op]) && $this->config[$op] === true) {
            $this->debugSection(self::DRUPAL_USER_REGISTRY_MODULE_NAME, $msg);
            $fn = "{$op}Users";
            $this->testUserManager->$fn($this->drupalTestUsers);
        }
    }

    /**
     * Gets the user who is currently logged in, or null if there isn't one.
     *
     * @return DrupalTestUser|null
     */
    public function getLoggedInUser()
    {
        return $this->loggedInUser;
    }

    /**
     * Sets the user who is currently logged in.
     *
     * @param DrupalTestUser $person
     */
    public function setLoggedInUser($person)
    {
        $this->loggedInUser = $person;
    }

    /**
     * Removes the currently logged in user, if there is one, and sets it back to null.
     */
    public function removeLoggedInUser()
    {
        $this->loggedInUser = null;
    }
}
