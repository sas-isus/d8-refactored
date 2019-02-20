<?php

namespace Drupal\permissions_by_term\Service;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Database\Connection;
use Drupal\permissions_by_term\Event\PermissionsByTermDeniedEvent;
use Drupal\user\Entity\User;
use Drupal\taxonomy\Entity\Term;

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
   * @param int $nid
   * @param bool $uid
   * @param string $langcode
   *
   * @return array|bool
   */
  public function canUserAccessByNodeId($nid, $uid = FALSE, $langcode = '') {
		$langcode = ($langcode === '') ? \Drupal::languageManager()->getCurrentLanguage()->getId() : $langcode;

    if (\Drupal::currentUser()->hasPermission('bypass node access')) {
      return TRUE;
    }

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
      $termInfo = Term::load($term->tid);

      if ($termInfo instanceof Term && $termInfo->get('langcode')->getLangcode() == $langcode) {
        $access_allowed = $this->isAccessAllowedByDatabase($term->tid, $uid, $termInfo->get('langcode')->getLangcode());
        if (!$access_allowed) {
          if ($singleTermRestriction) {
            return $access_allowed;
          }
        }

        if ($access_allowed && !$singleTermRestriction) {
          return $access_allowed;
        }
      }

    }

    return $access_allowed;
  }

  /**
   * @param int      $tid
   * @param bool|int $uid
   * @param string   $langcode
   * @return bool
   */
  public function isAccessAllowedByDatabase($tid, $uid = FALSE, $langcode = '') {
		$langcode = ($langcode === '') ? \Drupal::languageManager()->getCurrentLanguage()->getId() : $langcode;

    if ($uid === FALSE || (int) $uid === 0) {
      $user = \Drupal::currentUser();
    } elseif (is_numeric($uid)) {
      $user = User::load($uid);
    }

    $tid = (int) $tid;

    if (!$this->isAnyPermissionSetForTerm($tid, $langcode)) {
      return TRUE;
    }

    /* At this point permissions are enabled, check to see if this user or one
     * of their roles is allowed.
     */
    $aUserRoles = $user->getRoles();

    foreach ($aUserRoles as $sUserRole) {

      if ($this->isTermAllowedByUserRole($tid, $sUserRole, $langcode)) {
        return TRUE;
      }

    }

    $iUid = intval($user->id());

    if ($this->isTermAllowedByUserId($tid, $iUid, $langcode)) {
      return TRUE;
    }

    return FALSE;

  }

  /**
   * @param int    $tid
   * @param int    $iUid
   * @param string $langcode
   *
   * @return bool
   */
  private function isTermAllowedByUserId($tid, $iUid, $langcode) {
    $query_result = $this->database->query("SELECT uid FROM {permissions_by_term_user} WHERE tid = :tid AND uid = :uid AND langcode = :langcode",
      [':tid' => $tid, ':uid' => $iUid, ':langcode' => $langcode])->fetchField();

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
   * @param string $langcode
   *
   * @return bool
   */
  public function isTermAllowedByUserRole($tid, $sUserRole, $langcode) {
    $query_result = $this->database->query("SELECT rid FROM {permissions_by_term_role} WHERE tid = :tid AND rid IN (:user_roles) AND langcode = :langcode",
      [':tid' => $tid, ':user_roles' => $sUserRole, ':langcode' => $langcode])->fetchField();

    if (!empty($query_result)) {
      return TRUE;
    }
    else {
      return FALSE;
    }

  }

  /**
   * @param int    $tid
   * @param string $langcode
   *
   * @return bool
   */
  public function isAnyPermissionSetForTerm($tid, $langcode = '') {
		$langcode = ($langcode === '') ? \Drupal::languageManager()->getCurrentLanguage()->getId() : $langcode;

    $iUserTableResults = intval($this->database->query("SELECT COUNT(1) FROM {permissions_by_term_user} WHERE tid = :tid AND langcode = :langcode",
      [':tid' => $tid, ':langcode' => $langcode])->fetchField());

    $iRoleTableResults = intval($this->database->query("SELECT COUNT(1) FROM {permissions_by_term_role} WHERE tid = :tid AND langcode = :langcode",
      [':tid' => $tid, ':langcode' => $langcode])->fetchField());

    if ($iUserTableResults > 0 ||
      $iRoleTableResults > 0) {
      return TRUE;
    }

  }

  /**
   * @param string $nodeId
   * @param string $langcode
   *
   * @return AccessResult
   */
  public function handleNode($nodeId, $langcode) {
    if ($this->canUserAccessByNodeId($nodeId, false, $langcode) === TRUE) {
      return AccessResult::neutral();
    }
    else {
      $accessDeniedEvent = new PermissionsByTermDeniedEvent($nodeId);
      $this->eventDispatcher->dispatch(PermissionsByTermDeniedEvent::NAME, $accessDeniedEvent);

      return AccessResult::forbidden();
    }
  }

}
