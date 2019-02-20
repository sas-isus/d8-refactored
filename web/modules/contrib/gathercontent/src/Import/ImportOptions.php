<?php

namespace Drupal\gathercontent\Import;

/**
 * A class for storing and serializing the import/update options of a node.
 */
class ImportOptions {

  /**
   * Decides how to import the node.
   *
   * @var string
   *
   * @see \Drupal\gathercontent\Import\NodeUpdateMethod
   */
  public $nodeUpdateMethod = NodeUpdateMethod::ALWAYS_UPDATE;

  /**
   * Decides to create a new revision or not.
   *
   * @var bool
   */
  public $createNewRevision = FALSE;

  /**
   * Decides whether to publish the imported node.
   *
   * @var bool
   */
  public $publish = FALSE;

  /**
   * ID of a GatherContent status.
   *
   * If set, status of the imported node will be updated both in GatherContent and Drupal.
   *
   * @var int
   */
  public $newStatus = NULL;

  /**
   * ID of a Drupal menu item.
   *
   * If set, imported node will be a menu item.
   *
   * @var string
   */
  public $parentMenuItem = NULL;

  /**
   * The UUID of the Operation entity this node import's OperationItem entity connects to.
   */
  public $operationUuid = NULL;

  /**
   * ImportOptions constructor.
   */
  public function __construct(
    $node_update_method = NodeUpdateMethod::ALWAYS_UPDATE,
    $publish = FALSE,
    $create_new_revision = FALSE,
    $new_status = NULL,
    $parent_menu_item = NULL,
    $operation_uuid = NULL
  ) {
    $this->nodeUpdateMethod = $node_update_method;
    $this->createNewRevision = $create_new_revision;
    $this->publish = $publish;
    $this->newStatus = filter_var($new_status, FILTER_VALIDATE_INT);
    $this->parentMenuItem = $parent_menu_item;
    $this->operationUuid = $operation_uuid;
  }

  /**
   * Getter $nodeUpdateMethod.
   */
  public function getNodeUpdateMethod() {
    return $this->nodeUpdateMethod;
  }

  /**
   * Setter $nodeUpdateMethod.
   */
  public function setNodeUpdateMethod($nodeUpdateMethod) {
    $this->nodeUpdateMethod = $nodeUpdateMethod;
    return $this;
  }

  /**
   * Getter $createNewRevision.
   */
  public function getCreateNewRevision() {
    return $this->createNewRevision;
  }

  /**
   * Setter $createNewRevision.
   */
  public function setCreateNewRevision($createNewRevision) {
    $this->createNewRevision = $createNewRevision;
    return $this;
  }

  /**
   * Getter $publish.
   */
  public function getPublish() {
    return $this->publish;
  }

  /**
   * Setter $publish.
   */
  public function setPublish($publish) {
    $this->publish = $publish;
    return $this;
  }

  /**
   * Getter $newStatus.
   */
  public function getNewStatus() {
    return $this->newStatus;
  }

  /**
   * Setter $newStatus.
   */
  public function setNewStatus($new_status) {
    $this->newStatus = $new_status;
    return $this;
  }

  /**
   * Getter $parentMenuItem.
   */
  public function getParentMenuItem() {
    return $this->parentMenuItem;
  }

  /**
   * Setter $parentMenuItem.
   */
  public function setParentMenuItem($parent_menu_item) {
    $this->parentMenuItem = $parent_menu_item;
    return $this;
  }

  /**
   * Getter $operation_uuid.
   */
  public function getOperationUuid() {
    return $this->operationUuid;
  }

  /**
   * Setter $operation_uuid.
   */
  public function setOperationUuid($operation_uuid) {
    $this->operationUuid = $operation_uuid;
    return $this;
  }

}
