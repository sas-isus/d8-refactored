<?php

namespace Drupal\gathercontent\Entity;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface for defining Gathercontent operation item entities.
 *
 * @ingroup gathercontent
 */
interface OperationItemInterface extends ContentEntityInterface {

  /**
   * Getter for status property.
   *
   * @return string
   *   Status value.
   */
  public function getStatus();

  /**
   * Getter for item status color property.
   *
   * @return string
   *   Hex value for status color.
   */
  public function getItemStatusColor();

  /**
   * Getter for item status property.
   *
   * @return string
   *   Item status value.
   */
  public function getItemStatus();

}
