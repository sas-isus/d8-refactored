<?php

namespace Drupal\permissions_by_term\Service;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Database\Connection;
use Drupal\user\Entity\User;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\permissions_by_term\Event\PermissionsByTermDeniedEvent;

/**
 * AccessCheckService class.
 */
class AccessCheck {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * @var ContainerAwareEventDispatcher
   */
  private $eventDispatcher;

  /**
   * Constructs AccessCheck object.
   *
   * @param Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database, ContainerAwareEventDispatcher $eventDispatcher) {
    $this->database  = $database;
    $this->eventDispatcher = $eventDispatcher;
  }

  /**
   * @return bool
   */
  public function canUserAccessByNodeId($nid, $uid = FALSE) {
    if (!$singleTermRestriction = \Drupal::config('permissions_by_term.settings.single_term_restriction')->get('value')) {
      $access_allowed = TRUE;
    } else {
      $access_allowed = FALSE;
    }

    $terms = $this->database
      ->query("SELECT tid FROM {taxonomy_index} WHERE nid = :nid",
      [':nid' => $nid])->fetchAll();

    if (empty($terms)) {
      return TRUE;
    }

    foreach ($terms as $term) {
      $access_allowed = $this->isAccessAllowedByDatabase($term->tid, $uid);
      if (!$access_allowed) {
        if ($singleTermRestriction) {
          return $access_allowed;
        }
      }

      if ($access_allowed && !$singleTermRestriction) {
        return $access_allowed;
      }

    }

    return $access_allowed;
  }

  /**
   * @param int      $tid
   * @param bool|int $uid
   * @return array
   */
  public function isAccessAllowedByDatabase($tid, $uid = FALSE) {

    if ($uid === FALSE) {
      $user = \Drupal::currentUser();
    } elseif (is_numeric($uid)) {
      $user = User::load($uid);
    }

    // Admin can access everything (user id "1").
    if ($user->id() == 1) {
      return TRUE;
    }

    $tid = intval($tid);

    if (!$this->isAnyPermissionSetForTerm($tid)) {
      return TRUE;
    }

    /* At this point permissions are enabled, check to see if this user or one
     * of their roles is allowed.
     */
    $aUserRoles = $user->getRoles();

    foreach ($aUserRoles as $sUserRole) {

      if ($this->isTermAllowedByUserRole($tid, $sUserRole)) {
        return TRUE;
      }

    }

    $iUid = intval($user->id());

    if ($this->isTermAllowedByUserId($tid, $iUid)) {
      return TRUE;
    }

    return FALSE;

  }

  /**
   * @param int $tid
   * @param int $iUid
   *
   * @return bool
   */
  private function isTermAllowedByUserId($tid, $iUid) {
    $query_result = $this->database->query("SELECT uid FROM {permissions_by_term_user} WHERE tid = :tid AND uid = :uid",
      [':tid' => $tid, ':uid' => $iUid])->fetchField();

    if (!empty($query_result)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * @param int    $tid
   * @param string $sUserRole
   *
   * @return bool
   */
  public function isTermAllowedByUserRole($tid, $sUserRole) {
    $query_result = $this->database->query("SELECT rid FROM {permissions_by_term_role} WHERE tid = :tid AND rid IN (:user_roles)",
      [':tid' => $tid, ':user_roles' => $sUserRole])->fetchField();

    if (!empty($query_result)) {
      return TRUE;
    }
    else {
      return FALSE;
    }

  }

  /**
   * @param int $tid
   *
   * @return bool
   */
  public function isAnyPermissionSetForTerm($tid) {

    $iUserTableResults = intval($this->database->query("SELECT COUNT(1) FROM {permissions_by_term_user} WHERE tid = :tid",
      [':tid' => $tid])->fetchField());

    $iRoleTableResults = intval($this->database->query("SELECT COUNT(1) FROM {permissions_by_term_role} WHERE tid = :tid",
      [':tid' => $tid])->fetchField());

    if ($iUserTableResults > 0 ||
      $iRoleTableResults > 0) {
      return TRUE;
    }

  }

  /**
   * @return AccessResult
   */
  public function handleNode($nodeId) {
    if ($this->canUserAccessByNodeId($nodeId) === TRUE) {
      return AccessResult::neutral();
    }
    else {
      $accessDeniedEvent = new PermissionsByTermDeniedEvent($nodeId);
      $this->eventDispatcher->dispatch(PermissionsByTermDeniedEvent::NAME, $accessDeniedEvent);

      return AccessResult::forbidden();
    }
  }

}
