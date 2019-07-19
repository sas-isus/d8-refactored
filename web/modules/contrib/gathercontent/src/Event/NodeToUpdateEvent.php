<?php

namespace Drupal\gathercontent\Event;

use Drupal\node\NodeInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Wraps a node to update event for event listeners.
 */
class NodeToUpdateEvent extends Event {

  /**
   * Node object.
   *
   * @var \Drupal\node\NodeInterface|bool
   */
  protected $node = FALSE;

  /**
   * Source fields.
   *
   * @var object
   */
  protected $sourceValues;

  /**
   * Files fetched from GatherContent.
   *
   * @var array
   */
  protected $files;

  /**
   * Constructs a node to update event object.
   *
   * @param object $sourceValues
   *   Source fields representing object in GatherContent.
   * @param array $files
   *   Array of files fetched from GatherContent.
   */
  public function __construct($sourceValues, array $files) {
    $this->sourceValues = $sourceValues;
    $this->files = $files;
  }

  /**
   * Sets the node object.
   *
   * @return \Drupal\node\NodeInterface
   *   The node object.
   */
  public function setNode(NodeInterface $node) {
    $this->node = $node;
  }

  /**
   * Gets the node object if set.
   *
   * @return \Drupal\node\NodeInterface|bool
   *   The node object or false.
   */
  public function getNode() {
    return $this->node;
  }

  /**
   * Gets the array of source fields.
   *
   * @return object
   *   Source fields.
   */
  public function getSourceValues() {
    return $this->sourceValues;
  }

  /**
   * Gets the array of source files.
   *
   * @return array
   *   Source files.
   */
  public function getFiles() {
    return $this->files;
  }

}
