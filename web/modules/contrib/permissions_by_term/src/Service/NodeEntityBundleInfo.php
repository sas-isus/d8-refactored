<?php

namespace Drupal\permissions_by_term\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Template\TwigEnvironment;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;

/**
 * Class Info
 *
 * @package Drupal\permissions_by_term\Service
 */
class NodeEntityBundleInfo {

  /**
   * @var AccessStorage
   */
  private $accessStorage;

  /**
   * @var TermHandler
   */
  private $term;

  /**
   * @var TwigEnvironment
   */
  private $twig;

  /**
   * @var Connection
   */
  private $database;

  /**
   * Info constructor.
   *
   * @param AccessStorage   $accessStorage
   * @param TermHandler            $term
   * @param TwigEnvironment $twig
   * @param Connection      $database
   */
  public function __construct(
    AccessStorage $accessStorage,
    TermHandler $term,
    TwigEnvironment $twig,
    Connection $database
  ) {
    $this->accessStorage = $accessStorage;
    $this->term = $term;
    $this->twig = $twig;
    $this->database = $database;
  }

  /**
   * @param $viewFilePath
   * @param null $nid
   * @param string $langcode
   *
   * @return string
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Runtime
   * @throws \Twig_Error_Syntax
   */
  public function renderNodeDetails($viewFilePath, $langcode, $nid = null) {
    $roles = null;
    $users = null;

    if (!empty($nid)) {
      $tids = $this->term->getTidsByNid($nid);
      if (!empty($tids)) {
        $uids = $this->accessStorage->getUserTermPermissionsByTids($tids, $langcode);
        $rids = $this->accessStorage->getRoleTermPermissionsByTids($tids, $langcode);
      }
    }

    if (!empty($rids)) {
      $roles = Role::loadMultiple($rids);
    }

    if (!empty($uids)) {
      $users = User::loadMultiple($uids);
    }

    $template = $this->twig->loadTemplate($viewFilePath);

    return $template->render(['roles' => $roles, 'users' => $users]);

  }

  /**
   * @return array
   */
  public function getPermissions()
  {
    $returnArray = null;

    $permittedUsers = $this->database->select('permissions_by_term_user', 'pu')
      ->fields('pu', ['uid', 'tid'])
      ->execute()
      ->fetchAll();

    $permittedRoles = $this->database->select('permissions_by_term_role', 'pr')
      ->fields('pr', ['rid', 'tid'])
      ->execute()
      ->fetchAll();

    if (!empty($permittedRoles)) {
      $returnArray['roleLabels'] = [];
      foreach ($permittedRoles as $permittedRole) {
        $role = Role::load($permittedRole->rid);
        if (!empty($role)) {
          $returnArray['roleLabels'][$permittedRole->tid][] = $role->label();
        }
      }
    }

    if (!empty($permittedUsers)) {
      $returnArray['userDisplayNames'] = [];
      foreach ($permittedUsers as $permittedUser) {
        $user = User::load($permittedUser->uid);
        if (!empty($user)) {
          $returnArray['userDisplayNames'][$permittedUser->tid][] = $user->getDisplayName();
        }
      }
    }

    return $returnArray;
  }

}
