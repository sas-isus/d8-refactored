<?php

namespace Drupal\permissions_by_term\Service\NodeAccessRecords;


use Drupal\permissions_by_term\Service\TermHandler;

class NodeTermDataCacher extends DatabaseCacherAbstract {

  /**
   * @var \Drupal\permissions_by_term\Service\TermHandler
   */
  private $termHandler;

  public function __construct(TermHandler $termHandler) {
    $this->termHandler = $termHandler;
  }

  public function getData(): array {
    // TODO: Implement getData() method.
  }



}
