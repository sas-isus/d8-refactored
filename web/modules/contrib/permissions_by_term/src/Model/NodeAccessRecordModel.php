<?php

namespace Drupal\permissions_by_term\Model;

class NodeAccessRecordModel {

  /**
   * @var int $nid
   */
  public $nid;

  /**
   * @var string $langcode
   */
  public $langcode;

  /**
   * @var int $fallback
   */
  public $fallback;

  /**
   * @var int $gid
   */
  public $gid;

  /**
   * @var string $realm
   */
  public $realm;

  /**
   * @var int $grant_view
   */
  public $grant_view;

  /**
   * @var int $grant_update
   */
  public $grant_update;

  /**
   * @var int $grant_delete
   */
  public $grant_delete;

  /**
   * @return int
   */
  public function getNid()
  {
    return $this->nid;
  }

  /**
   * @param int $nid
   */
  public function setNid($nid)
  {
    $this->nid = $nid;
  }

  /**
   * @return string
   */
  public function getLangcode()
  {
    return $this->langcode;
  }

  /**
   * @param string $langcode
   */
  public function setLangcode($langcode)
  {
    $this->langcode = $langcode;
  }

  /**
   * @return int
   */
  public function getFallback()
  {
    return $this->fallback;
  }

  /**
   * @param int $fallback
   */
  public function setFallback($fallback)
  {
    $this->fallback = $fallback;
  }

  /**
   * @return int
   */
  public function getGid()
  {
    return $this->gid;
  }

  /**
   * @param int $gid
   */
  public function setGid($gid)
  {
    $this->gid = $gid;
  }

  /**
   * @return string
   */
  public function getRealm()
  {
    return $this->realm;
  }

  /**
   * @param string $realm
   */
  public function setRealm($realm)
  {
    $this->realm = $realm;
  }

  /**
   * @return int
   */
  public function getGrantView()
  {
    return $this->grant_view;
  }

  /**
   * @param int $grant_view
   */
  public function setGrantView($grant_view)
  {
    $this->grant_view = $grant_view;
  }

  /**
   * @return int
   */
  public function getGrantUpdate()
  {
    return $this->grant_update;
  }

  /**
   * @param int $grant_update
   */
  public function setGrantUpdate($grant_update)
  {
    $this->grant_update = $grant_update;
  }

  /**
   * @return int
   */
  public function getGrantDelete()
  {
    return $this->grant_delete;
  }

  /**
   * @param int $grant_delete
   */
  public function setGrantDelete($grant_delete)
  {
    $this->grant_delete = $grant_delete;
  }

}