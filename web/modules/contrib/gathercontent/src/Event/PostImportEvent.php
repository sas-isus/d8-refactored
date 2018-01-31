<?php

namespace Drupal\gathercontent\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Wraps a post import event for event listeners.
 */
class PostImportEvent extends Event {

  /**
   * Array of arrays with successfully imported nids and their gc_ids.
   *
   * @var array
   */
  protected $successNodes;

  /**
   * Array of arrays with unsuccessfully imported nids and their gc_ids.
   *
   * @var array
   */
  protected $unsuccessNodes;

  /**
   * ID of operation.
   *
   * You can fetch \Drupal\gathercontent\Entity\Operation using this ID or you
   * can fetch all OperationItems related to Operation.
   *
   * @var int
   */
  protected $operationId;

  /**
   * Constructs a post import event object.
   *
   * @param array $success
   *   Array of arrays with successfully imported nids and their gc_ids.
   * @param array $unsuccess
   *   Array of arrays with unsuccessfully imported nids and their gc_ids.
   * @param string $operationId
   *   UUID of \Drupal\gathercontent\Entity\Operation entity.
   */
  public function __construct(array $success, array $unsuccess, $operationId) {
    $this->successNodes = $success;
    $this->unsuccessNodes = $unsuccess;
    $this->operationId = $operationId;
  }

  /**
   * Get array of arrays with successfully imported nodes.
   *
   * @return array
   *   Array of arrays with successfully imported nids and their gc_ids.
   */
  public function getSuccessNodes() {
    return $this->successNodes;
  }

  /**
   * Get array of arrays with unsuccessfully imported nodes.
   *
   * @return array
   *   Array of arrays with unsuccessfully imported nids and their gc_ids.
   */
  public function getUnsuccessNodes() {
    return $this->unsuccessNodes;
  }

  /**
   * Get operation ID property.
   *
   * @return string
   *   UUID of \Drupal\gathercontent\Entity\Operation entity
   */
  public function getOperationId() {
    return $this->operationId;
  }

}
