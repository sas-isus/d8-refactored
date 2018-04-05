<?php

namespace Drupal\permissions_by_entity\Service;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Interface AccessCheckerInterface.
 *
 * @package Drupal\permissions_by_entity\Service
 */
interface AccessCheckerInterface {

  /**
   * Checks if a user is allowed to access a content entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   * @param bool|int $uid
   *   (Optional) Defaults to the uid of the current user.
   *
   * @return bool TRUE if access is allowed, otherwise FALSE.
   * TRUE if access is allowed, otherwise FALSE.
   */
  public function isAccessAllowed(ContentEntityInterface $entity, $uid = FALSE);

}
