Drupal User Registry
===

## A Codeception module for managing test users

_Drupal User Registry_ is a [Codeception module](http://codeception.com/addons) for managing test users on [Drupal](https://www.drupal.org/) sites. It can be configured to automatically create users before and delete users after a suite run.

It also allows the use of the following statements in tests:

```php
// Returns a DrupalTestUser object representing the test user available for this role.
$user = $I->getUserByRole($roleName);

// Returns a DrupalTestUser object representing the user, or false if no users were found. Note this will only
// return a user defined and managed by this module, it will not return information about arbitrary accounts
// on the site being tested.
$user = $I->getUser($userName);

// Returns an indexed array of configured roles, for example:
//   array(
//     0 => 'administrator',
//     1 => 'editor',
//     2 => ...
//   );
$roles = $I->getRoles();

/// Returns a DrupalTestUser object representing the "root" user (account with uid 1), if credentials are configured:
$rootUser = $I->getRootUser();
```

The [DrupalTestUser](https://github.com/pfaocle/codeception-module-drupal-user-registry/blob/master/src/Drupal/UserRegistry/DrupalTestUser.php) class is a very minimal representation of a Drupal user account and can be used as part of a login procedure defined in, for example, a StepObject or PageObject.

This module currently uses Drush and Drush aliases to create, delete and add roles to user accounts. Note that the `--delete-content` option is used when deleting users, so any content created by that user account will also be removed.


## Install with Composer

This module is available on [Packagist](https://packagist.org/packages/pfaocle/codeception-module-drupal-user-registry) and can be installed with Composer:

```json
{
    "require": {
        "codeception/codeception": "2.0.*",
        "pfaocle/codeception-module-drupal-user-registry": "dev-master"
    }
}
```

Drupal User Registry minimally requires Codeception 2.0 and PHP 5.4


## Example suite configuration

```yaml
class_name: AcceptanceTester
modules:
    enabled:
        - PhpBrowser
        - AcceptanceHelper
        - DrupalUserRegistry
    config:
        PhpBrowser:
            url: 'http://localhost/myapp/'
        DrupalUserRegistry:
            roles: ['administrator', 'editor', 'sub editor', 'lowly-user', 'authenticated']  # A list of user roles.
            emails:
                - administrator: 'admin@example.com'
                - editor: 'editor@example.com'
            password: 'test123!'         # The password to use for all test users.
            create: true                 # Whether to create all defined test users at the start of the suite.
            delete: true                 # Whether to delete all defined test users at the end of the suite.
            drush-alias: '@mysite.local' # The Drush alias to use when managing users via DrushTestUserManager.
            root:
                username: root           # Username for user with uid 1.
                password: root           # Password for user with uid 1.
```

### Required configuration

* Configuration values for `roles` and `password` are required.
* `create` and `delete` are optional and are assumed to be `false` if not set.
* `drush-alias` is only currently required as [DrushTestUserManager](https://github.com/pfaocle/codeception-module-drupal-user-registry/blob/master/src/Drupal/UserRegistry/DrushTestUserManager.php) is the only class available for managing (creating/deleting) users.
* The `root` key and its `username` and `password` are only required if `$I->getRootUser()` is used.

### Derivate usernames

Note that only a list of user roles is defined - no specific usernames. This is because we only need a single representative user account for a given role performing an acceptance test. Each role defined in configuration maps directly to a single user with username derived from the role name. For example, the configuration above would result in the following usernames: _test.administrator_, _test.editor_, _test.sub.editor_, _test.lowly.user_, _test.authenticated_.

The derivative usernames are always prefixed with _test._ and have any character in the role name matching the regex `/(\s|-)/` (i.e. whitespace and hyphens) replaced with a full-stop character (`.`).

**Caution:** no test user is created when the "root" user is configured. If the `getRootUser()` method is to be used the username and password will need to be set to working credentials, **stored in plain text**.


## Troubleshooting

The module provides more verbose output when used with Codeception's `--debug` option. For example:

    $ vendor/bin/codecept run --debug

    [Drupal User Registry] Creating test users.
      Trying to create test user 'test.administrator' on '@mysite.local'.
      drush -y '@mysite.local' user-information 'test.administrator'
    Creating test user 'test.administrator' on '@mysite.local'.
      drush -y '@mysite.local' user-create 'test.administrator' --mail='test.administrator@example.com' --password='test123!'
      drush -y '@mysite.local' user-add-role 'administrator' --name='test.administrator'
      Trying to create test user 'test.editor' on '@mysite.local'.
      drush -y '@mysite.local' user-information 'test.editor'
    Creating test user 'test.editor' on '@mysite.local'.
      drush -y '@mysite.local' user-create 'test.editor' --mail='test.editor@example.com' --password='test123!'
      drush -y '@mysite.local' user-add-role 'editor' --name='test.editor'
    ...
    (Tests.)
    ...
      [Drupal User Registry] Deleting test users.
    Deleting test user test.administrator on @mysite.local.
      drush -y '@mysite.local' user-cancel test.administrator --delete-content
    Deleting test user test.editor on @mysite.local.
      drush -y '@mysite.local' user-cancel test.editor --delete-content
    ...


## Acknowledgements

Props to [Andy Rigby](https://github.com/ixisandyr) for the storage code and inspiration.
