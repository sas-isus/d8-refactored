<?php

namespace Drupal\permissions_by_entity\Service;

use Drupal\Core\Entity\FieldableEntityInterface;

/**
 * Class CheckedEntityCache.
 *
 * @package Drupal\permissions_by_entity\Service
 */
class CheckedEntityCache {

  /**
   * The checked entities.
   *
   * @var \Drupal\Core\Entity\FieldableEntityInterface[]
   */
  private $entities = [];

  /**
   * Returns if an entity has already been checked.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   A fieldable entity.
   *
   * @return bool
   *   Returns TRUE if the entity has already been checked, otherwise FALSE.
   */
  public function isChecked(FieldableEntityInterface $entity) {
    return in_array($entity, $this->entities, TRUE);
  }

  /**
   * Adds a fieldable entity to the cache.
   *
   * If the entity has already been added to the cache, nothing will be done.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   A fieldable entity.
   */
  public function add(FieldableEntityInterface $entity) {
    // In order to avoid duplicate entries we check if the entity is already in
    // the list of entities.
    if (!$this->isChecked($entity)) {
      $this->entities[] = $entity;
    }
  }

  /**
   * Clears the cache.
   *
   * All cached fieldable entities will be removed irretrievably from the cache.
   */
  public function clear() {
    $this->entities = [];
  }

}
