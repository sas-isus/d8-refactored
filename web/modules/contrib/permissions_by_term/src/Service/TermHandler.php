<?php

namespace Drupal\permissions_by_term\Service;

use Drupal\Component\Utility\Html;
use Drupal\Core\Database\Connection;
use Drupal\taxonomy\Entity\Term;

/**
 * Class Term
 *
 * @package Drupal\permissions_by_term\Service
 */
class TermHandler {

  /**
   * The database connection.
   *
   * @var Connection
   */
  private $database;

  /**
   * @var Term
   */
  private $term;

  /**
   * Term constructor.
   *
   * @param Connection $database
   */
  public function __construct(
    Connection $database
  ) {
    $this->database = $database;
  }

  /**
   * @param int $nid
   *
   * @return array
   */
  public function getTidsByNid($nid) {
    $query = $this->database->select('taxonomy_index', 'ti')
      ->fields('ti', ['tid'])
      ->condition('ti.nid', $nid);

    return $query->execute()
      ->fetchCol();
  }

  public function getTidsBoundToAllNids(): array {
    $query = $this->database->select('taxonomy_index', 'ti')
      ->fields('ti', ['tid', 'nid']);

    $nidToTids = [];

    $ret = $query->execute()
      ->fetchAll();

    foreach ($ret as $returnObject) {
      $nidToTids[$returnObject->nid][] = $returnObject->tid;
    }

    return $nidToTids;
  }

  /**
   * @param array $tids
   *
   * @return array
   */
  public function getNidsByTids($tids) {
    if (!empty($tids)) {
      $query = $this->database->select('taxonomy_index', 'ti')
          ->fields('ti', ['nid'])
          ->condition('ti.tid', $tids, 'IN');

      $nids = $query->execute()
        ->fetchCol();

      return array_unique($nids);
    }
    else {
      return [];
    }
  }

  /**
   * @param string $sTermName
   *
   * @return int|null
   */
  public function getTermIdByName($sTermName) {
    $sTermName = Html::decodeEntities($sTermName);
    $aTermId = \Drupal::entityQuery('taxonomy_term')
      ->condition('name', $sTermName . '%', 'LIKE')
      ->execute();

    if (!empty($aTermId)) {
      $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load(key($aTermId));
      if ($term instanceof TermEntity) {
        $this->setTerm($term);
      }

      return key($aTermId);
    }

    return null;
  }

  /**
   * @param int $term_id
   *
   * @return string
   */
  public function getTermNameById($term_id) {
    $term_name = \Drupal::entityQuery('taxonomy_term')
      ->condition('id', $term_id)
      ->execute();
    return key($term_name);
  }

  public function setTerm(TermEntity $term) {
    $this->term = $term;
  }

  /**
   * @return TermEntity
   */
  public function getTerm() {
    return $this->term;
  }

}
