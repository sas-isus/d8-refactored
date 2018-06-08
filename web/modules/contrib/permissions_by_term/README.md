CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Recommended Modules
 * Installation
 * Configuration
 * Testing
 * Maintainers


INTRODUCTION
------------

The Permissions by Term module extends Drupal by functionality for restricting
access to single nodes via taxonomy terms. Taxonomy term permissions can be
coupled to specific user accounts and/or user roles. Taxonomy terms are part of
the Drupal core functionality.

Since Permissions by Term is using Node Access Records, every other core system
will be restricted:
 
 * search
 * menus
 * views
 * nodes

 * For a full description of the module visit:
   https://www.drupal.org/project/permissions_by_term
   or
   https://www.drupal.org/docs/8/modules/permissions-by-term

 * To submit bug reports and feature suggestions, or to track changes visit:
   https://www.drupal.org/project/issues/permissions_by_term


REQUIREMENTS
------------

This module requires no modules outside of Drupal core.


RECOMMENDED MODULES
-------------------

Module ships with Permissions by Entity module that extends the functionality of
Permissions By Term to be able to limit the selection of specific taxonomy terms
by users or roles for an entity.

* Webform Permissions Term -
  https://www.drupal.org/project/webform_permissions_by_term


INSTALLATION
------------

 * Install the Permissions by Term module as you would normally install a
   contributed Drupal module. Visit https://www.drupal.org/node/1897420 for
   further information.


CONFIGURATION
-------------

    1. Navigate to Administration > Extend and enable the module.
    2. Navigate to Administration > Structure > Taxonomy to create or edit a
       taxonomy term and add permissions to it. You can edit permissions in the
       "Permissions" labeled form field set.
    3. Enter in allowed users with a comma separated list of user names will be
       able to access content, related to this taxonomy term.
    4. Select the user roles who will be able to access content, related to the
       taxonomy term. Save.


TESTING
-------

Behat testing:

 * composer.json config - Make sure that the dependencies for Behat testing are
   installed. Check your drupal's `composer.json` file for the following
   contents:
   ```"require": {
        "composer/installers": "^1.0.24",
        "wikimedia/composer-merge-plugin": "~1.4",
        "drupal/drupal-extension": "~3.0",
        "guzzlehttp/guzzle" : "^6.0@dev",
        "drupal/drupal-driver": "~1.0",
        "behat/behat": "^3.1",
        "behat/mink": "^1.7",
        "behat/mink-extension": "^2.2",
        "behat/mink-selenium2-driver": "^1.3",
        "behat/mink-goutte-driver": "^1.2"
    },

    ...

    "merge-plugin": {
        "include": [
            "core/composer.json",
            "modules/permissions_by_term/composer.json"
        ],
        "recurse": false,
        "replace": false,
        "merge-extra": false
    },

    ...

    "autoload": {
        "psr-4": {
            "Drupal\\Core\\Composer\\": "core/lib/Drupal/Core/Composer",
            "Drupal\\Tests\\permissions_by_term\\Behat\\Context\\": "modules/permissions_by_term/tests/src/Behat/Context"
        }
    },
    ```

 * behat.yml file: Use the file at `tests/src/Behat/behat.yml.dist` as a
   template for your needs. Copy and name it to `behat.yml` and change it's
   paths according to your needs.

 * Chromedriver: It is recommended to use the
   [Chromedriver](https://sites.google.com/a/chromium.org/chromedriver/) as the
   driver between your Google Chrome browser and Behat. Make sure, that the
   Chromedriver version matches your operating system and your Google Chrome
   browser version.

 * Quick testing with a SQlite database: In
   `permissions_by_term/tests/src/Behat/fixtures/db.sqlite` you can find a
   SQLite database to test from. It is a standard Drupal 8 installation with PbT
   module installed. That way each test run proceeds quicker, because it is one
   file instead an entire relational database.

Make sure that the path to the SQLite database is contained in your
`settings.php` file. PbT awaits that the path is
`/sites/default/db.sqlite`. E.g.:
``` $databases['default']['default'] = array (
      'database' => '/Users/peter/Dev/mamp/permissions-by-term/sites/default/db.sqlite',
      'prefix' => '',
      'namespace' => 'Drupal\\Core\\Database\\Driver\\sqlite',
      'driver' => 'sqlite',
    );
```

The database file location is fixed, because the DB gets wiped after each Behat
test suite run.


MAINTAINERS
-----------

 * Peter Majmesku - https://www.drupal.org/u/peter-majmesku
 * Janak Singh (dakku) - https://www.drupal.org/u/dakku

Supporting organiztion:

 * publicplan GmbH - https://www.drupal.org/publicplan-gmbh
