<?php

/**
 * Implements hook_easy_social_widget().
 *
 * Define additional Easy Social widgets.
 *
 * In addition to specifying the widget's name and any external css and js
 * includes, you're also expected to create a corresponding theme implementation
 * for each widget you define. By default to it's expected to be the widget's
 * machine_name prefixed by "easy_social_".
 *
 * @return array
 *   An array with widget definitions, keyed by machine_name.
 */
function hook_easy_social_widget() {
  return array(
    'example' => array(
      // Required. Widget human name. For administrative use only.
      'name' => t('Example widget'),
      // Scripts for this widget.
      // Each item is an array with script info, in the same format as
      // drupal_add_js(). They will get forwarded directly.
      'js' => array(
        array(
          'data' => '//platform.example.com/widgets.js',
          'type' => 'external',
        ),
      ),
      // Styles for this widget.
      // Each item is an array with style info, in the same format as
      // drupal_add_css(). They will get forwarded directly.
      'css' => array(
        array(
          'data' => '//platform.example.com/widgets.css',
          'type' => 'external',
        ),
      ),
    ),
    // Define as many more widgets as you want.
  );
}

/**
 * Implements hook_easy_social_widget_alter().
 *
 * Allow modules to alter Easy Social widget information.
 *
 * @param array
 *  An array of widget information, as defined in hook_easy_social_widget()
 */
function hook_easy_social_widget_alter(&$widgets) {
  $widgets['example']['name'] = 'Example Widget Altered';
}
