<?php

namespace Drupal\permissions_by_entity\Event;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class EntityFieldValueAccessDeniedEvent.
 *
 * @package Drupal\permissions_by_entity\Event
 */
class EntityFieldValueAccessDeniedEvent extends Event {

  /**
   * The field that contains the content entity.
   *
   * @var \Drupal\Core\Field\FieldItemListInterface
   */
  private $field;

  /**
   * The content entity.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  private $entity;

  /**
   * The user id.
   *
   * @var int
   */
  private $uid;

  /**
   * The current index.
   *
   * @var int
   */
  private $index;

  /**
   * Sets the field.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $field
   *   The field that contains the content entity.
   */
  public function setField(FieldItemListInterface $field) {
    $this->field = $field;
  }

  /**
   * Returns the field.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface
   *   The field that contains the content entity.
   */
  public function getField() {
    return $this->field;
  }

  /**
   * Sets the content entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   */
  public function setEntity(ContentEntityInterface $entity) {
    $this->entity = $entity;
  }

  /**
   * Returns the entity.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The content entity.
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * Sets the uid.
   *
   * @param int $uid
   *   The user id.
   */
  public function setUid($uid) {
    $this->uid = $uid;
  }

  /**
   * Returns the uid.
   *
   * @return int
   *   The user id.
   */
  public function getUid() {
    return $this->uid;
  }

  /**
   * Sets the index.
   *
   * @param int $index
   *   The current index.
   */
  public function setIndex($index) {
    $this->index = $index;
  }

  /**
   * Returns index.
   *
   * @return int
   *   The current index.
   */
  public function getIndex() {
    return $this->index;
  }
}
