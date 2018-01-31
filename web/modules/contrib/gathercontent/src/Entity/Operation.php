<?php

namespace Drupal\gathercontent\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the Gathercontent operation entity.
 *
 * @ingroup gathercontent
 *
 * @ContentEntityType(
 *   id = "gathercontent_operation",
 *   label = @Translation("Gathercontent operation"),
 *   base_table = "gathercontent_operation",
 *   admin_permission = "administer gathercontent operation entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "uuid",
 *     "uuid" = "uuid",
 *   }
 * )
 */
class Operation extends ContentEntityBase implements OperationInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Type'))
      ->setDescription(t('Operation Type.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ]);

    return $fields;
  }

}
