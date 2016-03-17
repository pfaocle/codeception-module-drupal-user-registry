<?php

namespace Codeception\Module\Drupal\UserRegistry\Command;

use Codeception\Configuration;
use Codeception\Exception\Module as ModuleException;
use Codeception\Module\Drupal\UserRegistry\DrushTestUserManager;
use Codeception\Module\Drupal\UserRegistry\Storage\ModuleConfigStorage;

/**
 * Class TestUsersCommandTrait.
 *
 * @package Codeception\Module\Drupal\UserRegistry\Command
 */
trait TestUsersCommandTrait
{
    /**
     * @var \Codeception\Module\Drupal\UserRegistry\DrupalTestUser[]
     *   Store an array of test users to be created or deleted.
     */
    protected $users;

    /**
     * @var \Codeception\Module\Drupal\UserRegistry\TestUserManagerInterface
     *   Store the test user manager being used to create or delete users.
     */
    protected $testUserManager;

    /**
     * @param string $suiteName
     * @param array $suiteSettings
     *
     * @throws ModuleException
     * @throws \Codeception\Exception\Configuration
     * @throws \Exception
     */
    protected function getTestUsers($suiteName, $suiteSettings = null)
    {
        if (!$suiteSettings) {
            $suiteSettings = Configuration::suiteSettings($suiteName, Configuration::config());
        }

        if (!isset($suiteSettings['modules']['config']['DrupalUserRegistry'])) {
            throw new ModuleException(
                __CLASS__,
                sprintf("Drupal User Registry is not configured correctly in suite '%s'.", $suiteName)
            );
        }

        $config = $suiteSettings['modules']['config']['DrupalUserRegistry'];

        $moduleConfigStorage = new ModuleConfigStorage($config);
        $this->users = $moduleConfigStorage->load();
        $this->testUserManager = new DrushTestUserManager($config, $moduleConfigStorage);
    }
}
