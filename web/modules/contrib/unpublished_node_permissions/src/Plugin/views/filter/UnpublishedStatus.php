<?php

namespace Drupal\unpublished_node_permissions\Plugin\views\filter;

use Drupal\node\Entity\NodeType;
use Drupal\node\Plugin\views\filter\Status;

/**
 * Filter by unpublished status.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("node_unpublished_status")
 */
class UnpublishedStatus extends Status {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $table = $this->ensureMyTable();

    $account = \Drupal::currentUser();

    $query_addition = [];
    foreach (NodeType::loadMultiple() as $type) {
      $type_id = $type->id();
      if ($account->hasPermission("view $type_id unpublished content")) {
        $query_addition[] = "$table.type = '$type_id'";
      }
    }

    // Original node_status query.
    $query = "$table.status = 1 OR ($table.uid = ***CURRENT_USER*** AND ***CURRENT_USER*** <> 0 AND ***VIEW_OWN_UNPUBLISHED_NODES*** = 1) OR ***BYPASS_NODE_ACCESS*** = 1";

    // Add every allowed unpublished content type in an OR group.
    // See \Drupal\node\Plugin\views\filter\Status.
    if (count($query_addition)) {
      $query .= ' OR ';
      $query .= '(' . implode(' OR ', $query_addition) . ')';
    }

    $this->query->addWhereExpression($this->options['group'], $query);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $contexts = parent::getCacheContexts();

    $contexts[] = 'user.roles';

    return $contexts;
  }

}
