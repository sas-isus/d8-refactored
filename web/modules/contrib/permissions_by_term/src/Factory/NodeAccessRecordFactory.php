<?php

namespace Drupal\permissions_by_term\Factory;

use Drupal\permissions_by_term\Model\NodeAccessRecordModel;

class NodeAccessRecordFactory {

  public function create($realm, $gid, $nid, $langcode = '', $grantUpdate, $grantDelete) {
		$langcode = ($langcode === '') ? \Drupal::languageManager()->getCurrentLanguage()->getId() : $langcode;

    $nodeAccessRecord = new NodeAccessRecordModel();
    $nodeAccessRecord->setNid($nid);
    $nodeAccessRecord->setFallback(1);
    $nodeAccessRecord->setGid($gid);
    $nodeAccessRecord->setGrantDelete($grantDelete);
    $nodeAccessRecord->setGrantUpdate($grantUpdate);
    $nodeAccessRecord->setGrantView(1);
    $nodeAccessRecord->setLangcode($langcode);
    $nodeAccessRecord->setRealm($realm);

    return $nodeAccessRecord;
  }

}