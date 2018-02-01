<?php

/**
 * @file
 * Contains gathercontent\gathercontent.views.inc..
 *
 * Provide a custom views field data that isn't tied to any other module. */

/**
 * Implements hook_views_data().
 */
function gathercontent_views_data() {
  // @TODO: rewrite
  $data['views']['table']['group'] = t('Custom Global');
  $data['views']['table']['join'] = [
    // #global is a special flag which allows a table to appear all the time.
    '#global' => [],
  ];

  $data['views']['gathercontent_content_link'] = [
    'title' => t('Gathercontent content link'),
    'help' => t('Provide a link to the GatherContent content instance'),
    'field' => [
      'id' => 'gathercontent_content_link',
    ],
  ];

  $data['views']['gathercontent_status_color_field'] = [
    'title' => t('Gathercontent status color'),
    'help' => t('Status indicator'),
    'field' => [
      'id' => 'gathercontent_status_color_field',
    ],
  ];

  $data['views']['content_link'] = [
    'title' => t('Content link'),
    'help' => t('Provide a link to the content instance'),
    'field' => [
      'id' => 'content_link',
    ],
  ];

  return $data;
}
