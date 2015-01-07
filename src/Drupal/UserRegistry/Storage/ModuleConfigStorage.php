<?php

namespace Codeception\Module\Drupal\UserRegistry\Storage;

use Codeception\Module\Drupal\UserRegistry\DrupalTestUser;
use Codeception\Exception\Module as ModuleException;

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
    protected $drupal_username_prefix = 'test';

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
     * Check for required module configuration and initialize.
     *
     * @param array $config
     *   Array containing the DrupalUserRegistry module configuration.
     */
    public function __construct($config)
    {
        $this->roles = $config['roles'];
        $this->password = $config['password'];
        if (isset($config['drupal_username_prefix'])) {
            if (strlen($config['drupal_username_prefix']) < 4) {
                throw new ModuleException(
                    __CLASS__,
                    "Drupal username prefix should contain at least 4 characters. (" . $config['drupal_username_prefix'] . ")"
                );
            } else
            {
              $this->drupal_username_prefix = (string)$config['drupal_username_prefix'];
            }
        }
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
                $userName = $this->drupal_username_prefix . "." . $roleNameSuffix;
                return new DrupalTestUser($userName, $this->password, $roleName);
            },
            array_combine($this->roles, $this->roles)
        );
    }
}
