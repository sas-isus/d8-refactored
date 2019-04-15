<?php

namespace Drupal\permissions_by_term\Model;

class NidToTidsModel {

  /**
   * @var string
   */
  private $nid;

  /**
   * @var array
   */
  private $tids;

  /**
   * @return string
   */
  public function getNid(): string {
    return $this->nid;
  }

  /**
   * @param string $nid
   */
  public function setNid(string $nid): void {
    $this->nid = $nid;
  }

  /**
   * @return array
   */
  public function getTids(): array {
    return $this->tids;
  }

  /**
   * @param array $tids
   */
  public function setTids(array $tids): void {
    $this->tids = $tids;
  }

}
