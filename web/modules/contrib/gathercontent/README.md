# About

This module integrates Gather Content into Drupal.

## Installation

### Dependencies

* Image
* File
* Views

### Optional dependecies

* [Tablesorter][tablesorter] for sortable tables.

### Tasks

1. Download and install dependent packages. You'll need [Composer][composer].
2. Download the most recent release of [Tablesorter][tablesorter releases],
and place it into `libraries/`. Rename it's directory to `tablesorter-mottie`.

## API
We provide several events, which you can find in
`gathercontent/src/Event/GatherContentEvents.php` file with additional
documentation.

[composer]: https://getcomposer.org/doc/00-intro.md#system-requirements
[tablesorter]: https://github.com/mottie/tablesorter
[tablesorter releases]: https://github.com/Mottie/tablesorter/releases

### Tests
Your bootstrap attribute in "phpunit.xml.dist" should point to "web/core/tests/bootstrap.php".
Add this to the xml too with your credentials:

    <php>
        <env name="SIMPLETEST_DB" value="mysql://root:mysql@127.0.0.1/gc-drupal"/>
    </php>

Standing in the phpunit.xml.dist directory, run the tests as:
path/to/phpunit path/to/gathercontent/