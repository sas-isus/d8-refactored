<?php

namespace Drupal\gathercontent\Event;

use Drupal\node\NodeInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Wraps a pre node save event for event listeners.
 */
class PreNodeSaveEvent extends Event {

  /**
   * Node object.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

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
   * Constructs a pre node save event object.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Map plugin.
   * @param object $sourceValues
   *   Source fields representing object in GatherContent.
   * @param array $files
   *   Array of files fetched from GatherContent.
   */
  public function __construct(NodeInterface $node, $sourceValues, array $files) {
    $this->node = $node;
    $this->sourceValues = $sourceValues;
    $this->files = $files;
  }

  /**
   * Gets the node object.
   *
   * @return \Drupal\node\NodeInterface
   *   The node object.
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
