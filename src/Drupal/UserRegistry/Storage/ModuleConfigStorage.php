<?php

namespace Codeception\Module\Drupal\UserRegistry\Storage;

use Codeception\Module\Drupal\UserRegistry\DrupalTestUser;

/**
 * Class ModuleConfigStorage.
 *
 * @package Codeception\Module\DrupalUserRegistry\Storage
 */
class ModuleConfigStorage implements StorageInterface
{
    /**
     * This regex will be used in preg_replace(), replacing all matches with a full stop. For example, 'forum moderator'
     * becomes 'forum.moderator' and 'high-level administrator' would become 'high.level.administrator'. This is used
     * in conjunction with DRUPAL_USERNAME_PREFIX to create the test users' usernames.
     */
    const DRUPAL_ROLE_TO_USERNAME_PATTERN = '/(\s|-)/';

    /**
     * This string will be used as a prefix for a test user name in conjunction with the replacement pattern above. The
     * examples above will have usernames 'test.forum.moderator' and 'test.high.level.administrator' respectively.
     */
    const DRUPAL_USERNAME_PREFIX = 'test';

    /**
     * @var array
     *   Indexed array of Drupal role machine names.
     */
    protected $roles;

    /**
     * @var string
     *   Password to use for all test users.
     */
    protected $password;

    /**
     * Indexed array of email addresses, where the key is the role name.
     *
     * @var string
     */
    protected $emails;

    /**
     * Check for required module configuration and initialize.
     *
     * @param array $config
     *   Array containing the DrupalUserRegistry module configuration.
     */
    public function __construct($config)
    {
        $this->roles = $config['roles'];
        $this->emails = $config['emails'];
        $this->password = $config['password'];
    }

    /**
     * Load and return an array of test users.
     *
     * {@inheritdoc}
     */
    public function load()
    {
        return array_map(
            function ($roleName) {
                $roleNameSuffix = preg_replace(self::DRUPAL_ROLE_TO_USERNAME_PATTERN, ".", $roleName);
                $userName = self::DRUPAL_USERNAME_PREFIX . "." . $roleNameSuffix;

                // If an email address has been provided, set one.
                $email = isset($this->emails[$roleName]) ? $this->emails[$roleName] : null;

                return new DrupalTestUser($userName, $this->password, $roleName, $email);
            },
            array_combine($this->roles, $this->roles)
        );
    }
}
