<?php

namespace Drupal\gathercontent\Import;

use Drupal\gathercontent\Entity\OperationItem;
use Drupal\node\Entity\Node;

/**
 * Constants specifying how to import/update nodes.
 *
 * Methods relating to these constants.
 */
class NodeUpdateMethod {
  const ALWAYS_CREATE = 'always_create';
  const UPDATE_IF_NOT_CHANGED = 'update_if_not_changed';
  const ALWAYS_UPDATE = 'always_update';

  /**
   * Get Node object based on type of update.
   *
   * @param int $gc_id
   *   ID of item in GatherContent.
   * @param string $node_update_method
   *   Name of the node update method.
   * @param int $node_type_id
   *   ID of the node type.
   * @param string $langcode
   *   Language of translation if applicable.
   *
   * @return \Drupal\node\NodeInterface
   *   Return loaded node.
   */
  public static function getDestinationNode($gc_id, $node_update_method, $node_type_id, $langcode) {
    switch ($node_update_method) {
      case NodeUpdateMethod::UPDATE_IF_NOT_CHANGED;
        $result = \Drupal::entityQuery('node')
          ->condition('gc_id', $gc_id)
          ->sort('created', 'DESC')
          ->range(0, 1)
          ->execute();

        if ($result) {
          $node = Node::load(reset($result));
          $query_result = \Drupal::entityQuery('gathercontent_operation_item')
            ->condition('gc_id', $gc_id)
            ->sort('changed', 'DESC')
            ->range(0, 1)
            ->execute();

          $operation = OperationItem::load(reset($query_result));

          if ($node->getChangedTime() === $operation->getChangedTime()) {
            return $node;
          }
        }

        break;

      case NodeUpdateMethod::ALWAYS_UPDATE;
        $result = \Drupal::entityQuery('node')
          ->condition('gc_id', $gc_id)
          ->sort('created', 'DESC')
          ->range(0, 1)
          ->execute();

        if ($result) {
          return Node::load(reset($result));
        }

        break;

    }

    return Node::create([
      'type' => $node_type_id,
      'langcode' => $langcode,
    ]);
  }

}
