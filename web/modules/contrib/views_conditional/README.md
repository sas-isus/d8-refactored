CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

Views Conditional is a simple module that allows you to define conditionals
(if xxx then yyy) with fields in views. Conditions include:

 * Equal To
 * Not Equal To
 * Greater Than
 * Greater Than or Equals
 * Less Than
 * Less Than or Equals
 * Empty
 * Not Empty
 * Contains
 * Does Not Contain
 * Length Equal To
 * Length Not Equal To
 * Length Greater Than
 * Length Less Than

Views conditional allows you to output text based on the result of the
condition.


 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/views_conditional

 * To submit bug reports and feature suggestions, or to track changes:
   https://www.drupal.org/project/issues/views_conditional


REQUIREMENTS
------------

This module requires no modules outside of Drupal core.


INSTALLATION
------------

 * Install the Views Conditional module as you would normally install a
   contributed Drupal module. Visit https://www.drupal.org/node/1897420 for
   further information.


CONFIGURATION
-------------

    1. Navigate to Administration > Extend and enable the module.
    2. Navigate to Administration > Structure > Views and create a view.
       Add some fields under "FIELDS".
    3. Select "ADD" next to fields. Scroll down to "Views: Views Conditional",
       found near the bottom of the list.
    4. Add and configure fields.
    5. Choose a field to run a condition against, and provide values
       accordingly.
    6. Save, views conditional handles the logic and returns as specified.


MAINTAINERS
-----------

 * Shelane French - https://www.drupal.org/u/shelane
 * Anand Toshniwal - https://www.drupal.org/u/anandtoshniwal93
 * Timofey Denisov - https://www.drupal.org/u/ofry
 * MChittenden - https://www.drupal.org/u/mchittenden

Supporting organization:

 * QED42 - https://www.drupal.org/qed42
