<?php

namespace Drupal\permissions_by_term\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;
use Drupal\permissions_by_term\Factory\NodeAccessRecordFactory;
use Drupal\permissions_by_term\Model\NodeAccessRecordModel;
use Drupal\user\Entity\User;

/**
 * Class NodeAccess
 *
 * @package Drupal\permissions_by_term
 */
class NodeAccess {

  /**
   * @var int $uniqueGid
   */
  private $uniqueGid = 0;

  /**
   * @var AccessStorage $accessStorage
   */
  private $accessStorage;

  /**
   * @var User $userEntityStorage
   */
  private $userEntityStorage;

  /**
   * @var Node $node
   */
  private $node;

  /**
   * @var EntityTypeManagerInterface $entityTypeManager
   */
  private $entityTypeManager;

  /**
   * @var AccessCheck $accessCheck
   */
  private $accessCheck;

  /**
   * @var int $loadedUid
   */
  private $loadedUid;

  /**
   * @var User $userInstance
   */
  private $userInstance;

  /**
   * The database connection.
   *
   * @var Connection
   */
  private $database;

  /**
   * NodeAccess constructor.
   *
   * @param AccessStorage           $accessStorage
   * @param NodeAccessRecordFactory $nodeAccessRecordFactory
   * @param EntityTypeManagerInterface           $entityTypeManager
   * @param AccessCheck             $accessCheck
   * @param Connection              $database
   */
  public function __construct(
    AccessStorage $accessStorage,
    NodeAccessRecordFactory $nodeAccessRecordFactory,
    EntityTypeManagerInterface $entityTypeManager,
    AccessCheck $accessCheck,
    Connection $database
  ) {
    $this->accessStorage = $accessStorage;
    $this->nodeAccessRecordFactory = $nodeAccessRecordFactory;
    $this->entityTypeManager = $entityTypeManager;
    $this->userEntityStorage = $this->entityTypeManager->getStorage('user');
    $this->node = $this->entityTypeManager->getStorage('node');
    $this->accessCheck = $accessCheck;
    $this->database = $database;
  }

  /**
   * @return NodeAccessRecordModel
   */
  public function createGrant($nid, $gid) {
    return $this->nodeAccessRecordFactory->create(
      AccessStorage::NODE_ACCESS_REALM,
      $gid,
      $nid,
      $this->accessStorage->getLangCode($nid),
      0,
      0
    );
  }

  /**
   * @return int
   */
  public function getUniqueGid() {
    return $this->uniqueGid;
  }

  /**
   * @param int $uniqueGid
   */
  public function setUniqueGid($uniqueGid) {
    $this->uniqueGid = $uniqueGid;
  }

  public function canUserBypassNodeAccess($uid) {
    $user = $this->getUserInstance($uid);
    if ($user->hasPermission('bypass node access')) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * @param $uid
   * @param $nodeType
   * @param $nid
   *
   * @return bool
   */
  public function canUserDeleteNode($uid, $nodeType, $nid) {
    $user = $this->getUserInstance($uid);
    if ($user->hasPermission('delete any ' . $nodeType . ' content')) {
      return TRUE;
    }

    if ($this->isNodeOwner($nid, $uid) && $this->canDeleteOwnNode($uid, $nodeType)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * @param $nid
   * @param $uid
   *
   * @return bool
   */
  public function isNodeOwner($nid, $uid) {
    $node = $this->node->load($nid);
    if ((int)$node->getOwnerId() == (int)$uid) {
      return TRUE;
    }

    return FALSE;
  }

  private function canUpdateOwnNode($uid, $nodeType) {
    $user = $this->getUserInstance($uid);
    if ($user->hasPermission('edit own ' . $nodeType . ' content')) {
      return 1;
    }

    return 0;
  }

  private function canDeleteOwnNode($uid, $nodeType) {
    $user = $this->getUserInstance($uid);
    if ($user->hasPermission('delete own ' . $nodeType . ' content')) {
      return 1;
    }

    return 0;
  }

  /**
   * @param $nid
   *
   * @return array
   */
  public function getGrantsByNid($nid) {
    $grants = [];
    foreach ($this->grants as $grant) {
      if ($grant->nid == $nid) {
        $grants[] = $grant;
      }
    }

    return $grants;
  }

  /**
   * @return int
   */
  public function getLoadedUid() {
    return $this->loadedUid;
  }

  /**
   * @param int $loadedUid
   */
  public function setLoadedUid($loadedUid) {
    $this->loadedUid = $loadedUid;
  }

  /**
   * @return User
   */
  public function getUserInstance($uid) {
    if ($this->getLoadedUid() !== $uid) {
      $user = $this->userEntityStorage->load($uid);
      $this->setUserInstance($user);
      return $user;
    }

    return $this->userInstance;
  }

  /**
   * @param User $userInstance
   */
  public function setUserInstance($userInstance) {
    $this->userInstance = $userInstance;
  }

  /**
   * @param int $nid
   *
   * @return bool
   */
  public function isAccessRecordExisting($nid) {
    $query = $this->database->select('node_access', 'na')
      ->fields('na', ['nid'])
      ->condition('na.nid', $nid)
      ->condition('na.realm', AccessStorage::NODE_ACCESS_REALM);

    $result = $query->execute()
      ->fetchCol();

    if (empty($result)) {
      return FALSE;
    }

    return TRUE;
  }

}
