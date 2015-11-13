Drupal User Registry
===

## A Codeception module for managing test users

[![Build Status](https://travis-ci.org/ixis/codeception-module-drupal-user-registry.svg?branch=feature/add-tests)](https://travis-ci.org/ixis/codeception-module-drupal-user-registry) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ixis/codeception-module-drupal-user-registry/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ixis/codeception-module-drupal-user-registry/?branch=master) [![Latest Stable Version](https://poser.pugx.org/pfaocle/codeception-module-drupal-user-registry/v/stable.svg)](https://packagist.org/packages/pfaocle/codeception-module-drupal-user-registry) [![Latest unstable version](https://poser.pugx.org/pfaocle/codeception-module-drupal-user-registry/v/unstable.svg)](https://packagist.org/packages/pfaocle/codeception-module-drupal-user-registry) [![Total Downloads](https://poser.pugx.org/pfaocle/codeception-module-drupal-user-registry/downloads)](https://packagist.org/packages/pfaocle/codeception-module-drupal-user-registry) [![License](https://poser.pugx.org/pfaocle/codeception-module-drupal-user-registry/license.svg)](https://packagist.org/packages/pfaocle/codeception-module-drupal-user-registry)

_Drupal User Registry_ is a [Codeception module](http://codeception.com/addons) for managing test users on [Drupal](https://www.drupal.org/) sites. It can be configured to automatically create users before and delete users after a suite run.

It also allows the use of the following statements in tests:

```php
// Returns a DrupalTestUser object representing the test user available for
// this role.
$user = $I->getUserByRole($roleName);

// Returns a DrupalTestUser object representing the user, or false if no users
// were found. Note this will only return a user defined and managed by this
// module, it will not return information about arbitrary accounts on the site
// being tested.
$user = $I->getUser($userName);

// Returns an indexed array of configured roles, for example:
//   array(
//     0 => 'administrator',
//     1 => 'editor',
//     2 => ...
//   );
$roles = $I->getRoles();

// Returns a DrupalTestUser object representing the "root" user (account with
// uid 1), if credentials are configured:
$rootUser = $I->getRootUser();

// Also provided are a few utility methods that can be used in tests to
// store and retrieve a DrupalTestUser object representing the logged in user.
// Note these methods don't actually log a user in or out - that currently
// needs to be handled elsewhere.
$I->setLoggedInUser($I->getUserByRole('administrator'));
$I->getLoggedInUser();
$I->removeLoggedInUser();
```

All methods available to the Actor object `$I` are defined in this module's [public API](https://github.com/pfaocle/codeception-module-drupal-user-registry/blob/master/API.md).

The [DrupalTestUser](https://github.com/pfaocle/codeception-module-drupal-user-registry/blob/master/src/Drupal/UserRegistry/DrupalTestUser.php) class is a very minimal representation of a Drupal user account and can be used as part of a login procedure defined in, for example, a StepObject or PageObject.

This module currently uses Drush and Drush aliases to create, delete and add roles to user accounts. Note that the `--delete-content` option is used when deleting users, so any content created by that user account will also be removed.


## Installation

This module is available on [Packagist](https://packagist.org/packages/ixis/codeception-module-drupal-user-registry) and can be installed with Composer:

```json
{
    "require": {
        "codeception/codeception": "2.0.*",
        "ixis/codeception-module-drupal-user-registry": "~0.1.1"
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
            defaultPass: "foobar"
            users:
                administrator:
                    name: administrator
                    email: admin@example.com
                    pass: "foo%^&&"
                    roles: [ administrator, editor ]
                    root: true
                editor:
                    name: editor
                    email: editor@example.com
                    roles: [ editor, sub-editor ]
                "sub editor":
                    name: "sub editor"
                    email: "sub.editor@example.com"
                    roles: [ sub-editor ]
                authenticated:
                    name: authenticated
                    email: authenticated@example.com
                    roles: [ "authenticated user" ]
            create: true                 # Whether to create all defined test users at the start of the suite.
            delete: true                 # Whether to delete all defined test users at the end of the suite.
            drush-alias: '@mysite.local' # The Drush alias to use when managing users via DrushTestUserManager.
            username-prefix: robot       # Use this string instead of the default 'test' for prefixing test usernames.
```

### Required and optional configuration

Configured values for `users` are required. `drush-alias` is only currently required as [DrushTestUserManager](https://github.com/pfaocle/codeception-module-drupal-user-registry/blob/master/src/Drupal/UserRegistry/DrushTestUserManager.php) is the only class available for managing (creating/deleting) users.

Other optional configuration includes:

* `create` and `delete` are assumed to be `false` if not set.
* `defaultPass` can be used to set a default test user password in case you don't want to add a password for each user. It can still be overridden on a per-user basis.
* The `root` key can be added for any user (preferably just one) to indicate it is the root user (uid 1).

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


## Testing

This module has some unit and functional tests using Codeception. Currently only
the unit tests are run in Travis builds.

To run the unit tests:

    $ vendor/bin/codecept run unit

The functional suite requires a Drupal 7 site set up with an open connection to
the database as configured in **functional.suite.yml** - its best to edit the
configuration for the **local** environment and run:

    $ vendor/bin/codecept run functional --env=local

To run both suites:

    $ vendor/bin/codecept run --env=local


## Contribute

This module's code is managed with [git-flow (AVH Edition)](https://github.com/petervanderdoes/gitflow-avh).
Releases are made on the **master** branch and should be tagged using
[semantic versioning](http://semver.org/) and the format vx.y.z, e.g. v0.1.0

Pull requests should be made to the **develop** branch.

- Issue tracker: https://github.com/ixis/codeception-module-drupal-user-registry/issues
- Source code: https://github.com/ixis/codeception-module-drupal-user-registry


## Acknowledgements

Thanks to [Andy Rigby](https://github.com/ixisandyr) for the storage code and inspiration.


## License

The project is licensed under The MIT License (MIT).
