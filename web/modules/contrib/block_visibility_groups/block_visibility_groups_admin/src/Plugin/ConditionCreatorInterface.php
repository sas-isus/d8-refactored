<?php

namespace Drupal\block_visibility_groups_admin\Plugin;

/**
 *
 */
interface ConditionCreatorInterface {

  /**
   * Create condition elements for form.
   *
   * @return array
   */
  public function createConditionElements();

  /**
   * Create condition configuration from form submission.
   *
   * @param array $plugin_info
   *   The plugin implementation definition.
   *
   * @return array
   *   The condition config.
   */
  public function createConditionConfig($plugin_info);

  /**
   * Get the label when creating a new condition.
   *
   * @return string
   *   A new condition label.
   */
  public function getNewConditionLabel();

  /**
   * Determine if a condition was selected in the form.
   *
   * @param $condition_info
   *
   * @return bool
   *   TRUE if the condition was selected in a form.
   */
  public function itemSelected($condition_info);

}
