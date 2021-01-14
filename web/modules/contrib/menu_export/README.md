# Menu Export and Import

## CONTENTS OF THIS FILE

 * Introduction
 * Installation
 * Configuration
 * Maintainers

## INTRODUCTION

Export and Import menu ttems between Drupal instances, which is otherwise not supported by Configuration Management.

* For a full description of the module, visit the project page: https://drupal.org/project/menu_export
* To submit bug reports and feature suggestions, or to track changes: https://drupal.org/project/issues/menu_export

## INSTALLATION

* Install as usual, see https://www.drupal.org/docs/8/extending-drupal-8/installing-contributed-modules-find-import-enable-configure-drupal-8 for further information.

## CONFIGURATION

 * Visit "Menu Export" (`admin/config/development/menu_export`) on the source site.
 * Select menu(s) to export.
 * Visit "Export" tab (`admin/config/development/menu_export/export`) and press "Export" button, or use `drush config:export`
 * Copy exported configuration YAML to the target site.
 * On the target site, visit the "Import" tab (`admin/config/development/menu_export/import`) to import menus, or use `drush config:import`

## MAINTAINERS

Current maintainers:

 * Sandeep Reddy (https://www.drupal.org/u/sandeepguntaka)
