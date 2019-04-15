<?php

namespace Drupal\permissions_by_term\Service\NodeAccessRecords;


interface DatabaseCacherInterface {

  public function getData() : array;

  public function setData() : array;

}
