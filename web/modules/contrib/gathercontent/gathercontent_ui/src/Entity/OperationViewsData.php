<?php

namespace Drupal\gathercontent_ui\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Gathercontent operation entities.
 */
class OperationViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['gathercontent_operation']['table']['base'] = [
      'field' => 'id',
      'title' => $this->t('Gathercontent operation'),
      'help' => $this->t('The Gathercontent operation ID.'),
    ];

    return $data;
  }

}
