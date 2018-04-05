<?php

namespace Drupal\permissions_by_term\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class PermissionsByTermDeniedEvent
 *
 * @package Drupal\permissions_by_term\Event
 */
class PermissionsByTermDeniedEvent extends Event {
  const NAME = 'permissions_by_term.access.denied';

  /**
   * @var int
   */
  protected $nid;

  /**
   * PermissionsByTermDeniedEvent constructor.
   *
   * @param int $nid
   */
  public function __construct($nid) {
    $this->nid = $nid;
  }

  /**
   * @return int
   */
  public function getNid() {
    return $this->nid;
  }

}