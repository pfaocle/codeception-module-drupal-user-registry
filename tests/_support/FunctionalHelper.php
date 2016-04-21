<?php

namespace Codeception\Module;

use Codeception\Module\Drupal\UserRegistry\Storage\ModuleConfigStorage;

/**
 * Define custom actions for functional tests.
 *
 * All non-static public methods declared in helper class will be available in $I.
 */
class FunctionalHelper extends \Codeception\Module
{
    /**
     * Helper to translate role names to test usernames.
     *
     * @todo This code is copied from ModuleConfigStorage::load(), where it's a bit buried. Needs refactoring.
     *
     * @see ModuleConfigStorage::load()
     *
     * @param string $role
     *   The name of the role to translate into a test username.
     *
     * @return string
     */
    public function getTestUsername($role)
    {
        $roleNameSuffix = preg_replace(ModuleConfigStorage::DRUPAL_ROLE_TO_USERNAME_PATTERN, ".", $role);
        return ModuleConfigStorage::DRUPAL_USERNAME_PREFIX . "." . $roleNameSuffix;
    }
}
