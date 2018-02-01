<?php

/**
 * @file
 * GatherContent Upload drush command.
 */

use Drupal\gathercontent\Entity\Operation;
use Drupal\gathercontent\Entity\OperationItem;
use Drupal\node\Entity\Node;
use Drush\Log\LogLevel;

/**
 * Implements hook_drush_command().
 */
function gathercontent_upload_drush_command() {
  $commands = [];

  $commands['gathercontent-upload'] = [
    'aliases' => ['gc-u'],
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'description' => dt('Upload changes to GatherContent'),
    'engines' => [
      'outputformat' => [
        'default' => 'table',
        'pipe-format' => 'json',
        'output-data-type' => 'format-table',
        'field-labels' => [
          'id' => dt('ID'),
          'item_name' => dt('Item name'),
          'node_status' => dt('Node status'),
          'upload_status' => dt('Upload status'),
        ],
      ],
    ],
  ];

  return $commands;
}

/**
 * Implements drush_COMMAND().
 */
function drush_gathercontent_upload() {
  $operation = Operation::create([
    'type' => 'upload',
  ]);
  $operation->save();

  $node_ids = \Drupal::entityQuery('node')
    ->condition('gc_id', '', '<>')
    ->condition('gc_mapping_id', '', '<>')
    ->execute();

  $nodes = Node::loadMultiple($node_ids);

  $operations = [];
  foreach ($nodes as $node) {
    $operations[] = [
      'gathercontent_upload_process',
      [
        $node,
        $operation->uuid(),
      ],
    ];
  }

  $batch = [
    'title' => t('Uploading content ...'),
    'operations' => $operations,
    'finished' => 'gathercontent_drush_upload_finished',
    'init_message' => t('Upload is starting ...'),
    'progress_message' => t('Processed @current out of @total.'),
    'error_message' => t('An error occurred during processing'),
    'progressive' => TRUE,
  ];

  batch_set($batch);
  drush_backend_batch_process();

  $mappings = [];
  $operation_item_ids = \Drupal::entityQuery('gathercontent_operation_item')
    ->condition('operation_uuid', $operation->uuid())
    ->execute();
  if (!empty($operation_item_ids)) {
    $operation_items = OperationItem::loadMultiple($operation_item_ids);
    /** @var \Drupal\gathercontent\Entity\OperationItem $operation_item */
    foreach ($operation_items as $operation_item) {
      $mappings[$operation_item->id()] = [
        'id' => $operation_item->id(),
        'item_name' => $operation_item->item_name->value,
        'node_status' => $operation_item->getItemStatus(),
        'upload_status' => $operation_item->getStatus(),
      ];
    }
  }

  return $mappings;
}

/**
 * Batch process "finished" callback.
 */
function gathercontent_drush_upload_finished($success, $results, $operations) {
  if ($success) {
    drush_log(t('Upload finished'), LogLevel::OK);
  }
  else {
    drush_set_error('gathercontent_upload_failed', t('Upload failed'));
  }
}
