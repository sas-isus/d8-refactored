<?php

namespace Drupal\permissions_by_entity\Service;

use Drupal\Core\Entity\FieldableEntityInterface;

/**
 * Interface AccessCheckerInterface.
 *
 * @package Drupal\permissions_by_entity\Service
 */
interface AccessCheckerInterface {

  /**
   * Checks if a user is allowed to access a fieldable entity.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   A fieldable entity.
   * @param bool|int $uid
   *   (Optional) Defaults to the uid of the current user.
   *
   * @return bool
   *   TRUE if access is allowed, otherwise FALSE.
   */
  public function isAccessAllowed(FieldableEntityInterface $entity, $uid = FALSE);

  /**
   * Checks if the given entity uses terms with access restrictions.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   A fieldable entity.
   *
   * @return bool
   *   TRUE if entity is access controlled.
   *   FALSE if entity has no access restrictions.
   */
  public function isAccessControlled(FieldableEntityInterface $entity);

}
