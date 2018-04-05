Permissions by Term
====================================

Visit [Pbt's documentation page on Drupal.org](https://www.drupal.org/docs/8/modules/permissions-by-term) to
learn how to use this module.

## Modules which extend PbT's functionality
- [Webform Permissions By Term](https://www.drupal.org/project/webform_permissions_by_term)
This modules will be merged into PbT soon.
- [Permissions by Entity](https://www.drupal.org/project/permissions_by_entity)
This module is now merged into PbT

## Behat testing

### composer.json config
Make sure that the dependencies for Behat testing are installed. Check your drupal's `composer.json` file
for the following contents:

    "require": {
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
    
### behat.yml file
Use the file at `tests/src/Behat/behat.yml.dist` as a template for your needs. Copy and name it to `behat.yml` and change
it's paths according to your needs.
    
### Chromedriver
It is recommended to use the [Chromedriver](https://sites.google.com/a/chromium.org/chromedriver/) as the driver between
your Google Chrome browser and Behat. Make sure, that the Chromedriver version matches your operating system and your
Google Chrome browser version.

### Quick testing with a SQlite database
In permissions_by_term/tests/src/Behat/fixtures/db.sqlite` you can find a SQLite database to test from. It is a standard
Drupal 8 installation with PbT module installed. That way each test run proceeds quicker, because it is 1 file
instead an entire relational database.

Make sure that the path to the SQLite database is contained in your `settings.php` file. PbT awaits that the path is
`/sites/default/db.sqlite`. E.g.:

    $databases['default']['default'] = array (
      'database' => '/Users/peter/Dev/mamp/permissions-by-term/sites/default/db.sqlite',
      'prefix' => '',
      'namespace' => 'Drupal\\Core\\Database\\Driver\\sqlite',
      'driver' => 'sqlite',
    );

The database file location is fixed, because the DB gets wiped after each Behat test suite run.
