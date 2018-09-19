<?php
/**
 * @file
 * Easy Social theme functions.
 */

/**
 * Returns HTML for the Easy Social Pinterest widget.
 *
 * @ingroup themeable
 */
function theme_easy_social_pinterest($variables) {
  $attributes = array(
    'class' => array('pinterest-share'),
    'data-pin-do' => 'buttonPin',
  );

  $attributes['data-pin-config'] = $variables['config'];

  $build = array(
    '#theme' => 'link',
    '#text' => '<img src="//assets.pinterest.com/images/pidgets/pin_it_button.png" />',
    '#path' => '/pinterest.com/pin/create/button',
    '#options' => array(
      'attributes' => $attributes,
      'html' => TRUE,
      'query' => array(
        'url' => $variables['url'],
        'media' => $variables['image'],
        'description' => $variables['description'],
      ),
    ),
    '#prefix' => "\n" . '<span class="easy-social pinterest">',
    '#suffix' => '</span>',
  );

  return \Drupal::service('renderer')->render($build);
}
