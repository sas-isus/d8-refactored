<?php

namespace Drupal\gathercontent_ui\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Gathercontent operation item entities.
 */
class OperationItemViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['gathercontent_operation_item']['table']['base'] = [
      'field' => 'id',
      'title' => $this->t('Gathercontent operation item'),
      'help' => $this->t('The Gathercontent operation item ID.'),
    ];

    return $data;
  }

}
