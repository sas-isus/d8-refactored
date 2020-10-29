<?php

namespace Drupal\block_visibility_groups;

use Drupal\block_visibility_groups\Entity\BlockVisibilityGroup;

/**
 * Interface GroupEvaluatorInterface.
 *
 * @package Drupal\block_visibility_groups
 */
interface GroupEvaluatorInterface {

  /**
   * Evaluate Block Visibility Group.
   *
   * @param \Drupal\block_visibility_groups\Entity\BlockVisibilityGroup $block_visibility_group
   *   The block visibility group entity.
   *
   * @return bool
   *   TRUE if the group intends to be visible.
   */
  public function evaluateGroup(BlockVisibilityGroup $block_visibility_group);

}
