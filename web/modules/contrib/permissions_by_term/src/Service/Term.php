<?php

namespace Drupal\permissions_by_term\Service;

use Drupal\Core\Database\Connection;
use Drupal\taxonomy\Entity\Term as TermEntity;
use Drupal\Component\Utility\Html;

/**
 * Class Term
 *
 * @package Drupal\permissions_by_term\Service
 */
class Term {

  /**
   * The database connection.
   *
   * @var Connection
   */
  private $database;

  /**
   * @var TermEntity
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
   * @return int
   */
  public function getTermIdByName($sTermName) {
    $sTermName = Html::decodeEntities($sTermName);
    $aTermId = \Drupal::entityQuery('taxonomy_term')
      ->condition('name', $sTermName)
      ->execute();

    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load(key($aTermId));
    if ($term instanceof TermEntity) {
      $this->setTerm($term);
    }

    return key($aTermId);
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
