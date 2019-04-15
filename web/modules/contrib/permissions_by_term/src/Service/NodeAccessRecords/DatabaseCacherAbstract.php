<?php

namespace Drupal\permissions_by_term\Service\NodeAccessRecords;

use Drupal\Core\Session\AccountProxy;

abstract class DatabaseCacherAbstract implements DatabaseCacherInterface {

  private function fetchFromPrivateTempstore(): array {

  }

  private function setDataInPrivateTempStore(array $data): void {
    if (\Drupal::currentUser() instanceof AccountProxy && empty(self::$nodeAccessTids[$nid])) {
      /**
       * @var \Drupal\Core\TempStore\SharedTempStore $sharedTempstore
       */
      $sharedTempstore = $this->sharedTempStoreFactory->get('permissions_by_term');

      if (empty(self::$nodeAccessTids)) {
        $nodeAccess = $sharedTempstore->get('node_access');
        if (!empty($nodeAccess)) {
          self::$nodeAccessTids = $nodeAccess;
        }
      }
    }
  }

}
