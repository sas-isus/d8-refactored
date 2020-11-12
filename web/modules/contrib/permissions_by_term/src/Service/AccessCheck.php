<?php

namespace Drupal\permissions_by_term\Service;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Database\Connection;
use Drupal\permissions_by_term\Event\PermissionsByTermDeniedEvent;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;

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

  public function canUserAccessByNodeId($nid, $uid = FALSE, $langcode = ''): bool {
		$langcode = ($langcode === '') ? \Drupal::languageManager()->getCurrentLanguage()->getId() : $langcode;

    if (empty($uid)) {
      $uid = \Drupal::currentUser()->id();
    }

    $user = User::load($uid);
    if ($user instanceof User && $user->hasPermission('bypass node access')) {
      return TRUE;
    }

    $configPermissionMode = \Drupal::config('permissions_by_term.settings')->get('permission_mode');
    $requireAllTermsGranted = \Drupal::config('permissions_by_term.settings')->get('require_all_terms_granted');

    if (!$configPermissionMode && (!$requireAllTermsGranted)) {
      $access_allowed = TRUE;
    } else {
      $access_allowed = FALSE;
    }

    $terms = $this->database
      ->query("SELECT tid FROM {taxonomy_index} WHERE nid = :nid",
      [':nid' => $nid])->fetchAll();

    if (empty($terms) && !$configPermissionMode) {
      return TRUE;
    }

    foreach ($terms as $term) {
      $termInfo = Term::load($term->tid);

      if ($termInfo instanceof Term && $termInfo->get('langcode')->getLangcode() == $langcode) {
        if (!$this->isAnyPermissionSetForTerm($term->tid, $termInfo->get('langcode')->getLangcode())) {
          continue;
        }
        $access_allowed = $this->isAccessAllowedByDatabase($term->tid, $uid, $termInfo->get('langcode')->getLangcode());
        if (!$access_allowed && $requireAllTermsGranted) {
          return $access_allowed;
        }

        if ($access_allowed && !$requireAllTermsGranted) {
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

    if (is_numeric($uid) && $uid > 0) {
      $user = User::load($uid);
    } else {
      $user = \Drupal::currentUser();
    }

    $tid = (int) $tid;

    if (!$this->isAnyPermissionSetForTerm($tid, $langcode) && !\Drupal::config('permissions_by_term.settings')->get('permission_mode')) {
      return TRUE;
    }

    /* At this point permissions are enabled, check to see if this user or one
     * of their roles is allowed.
     */
    foreach ($user->getRoles() as $sUserRole) {

      if ($this->isTermAllowedByUserRole($tid, $sUserRole, $langcode)) {
        return TRUE;
      }

    }

    if ($this->isTermAllowedByUserId($tid, $user->id(), $langcode)) {
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

    $iUserTableResults = (int)$this->database->query("SELECT COUNT(1) FROM {permissions_by_term_user} WHERE tid = :tid AND langcode = :langcode",
      [':tid' => $tid, ':langcode' => $langcode])->fetchField();

    $iRoleTableResults = (int)$this->database->query("SELECT COUNT(1) FROM {permissions_by_term_role} WHERE tid = :tid AND langcode = :langcode",
      [':tid' => $tid, ':langcode' => $langcode])->fetchField();

    if ($iUserTableResults > 0 ||
      $iRoleTableResults > 0) {
      return TRUE;
    }

  }

  public function handleNode($nodeId, string $langcode): AccessResult {
    $result = AccessResult::neutral();

    if (!$this->canUserAccessByNodeId($nodeId, false, $langcode)) {
      $this->dispatchDeniedEvent($nodeId);

      $result = AccessResult::forbidden();
    }

    return $result;
  }

  public function dispatchDeniedEventOnRestricedAccess($nodeId, string $langcode): void {
    if (!$this->canUserAccessByNodeId($nodeId, false, $langcode)) {
      $this->dispatchDeniedEvent($nodeId);
    }
  }

  private function dispatchDeniedEvent($nodeId): void
  {
    $accessDeniedEvent = new PermissionsByTermDeniedEvent($nodeId);
    $this->eventDispatcher->dispatch(PermissionsByTermDeniedEvent::NAME, $accessDeniedEvent);
  }

}
