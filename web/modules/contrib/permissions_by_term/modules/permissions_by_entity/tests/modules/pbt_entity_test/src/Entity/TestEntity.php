<?php

namespace Drupal\pbt_entity_test\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines a content entity type that has a string ID.
 *
 * @ContentEntityType(
 *   id = "test_entity",
 *   label = @Translation("Test entity"),
 *   base_table = "test_entity_table",
 *   handlers = {
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   links = {
 *     "canonical" = "/test-entity/{test_entity}",
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "langcode" = "langcode",
 *     "published" = "published"
 *   }
 * )
 */
class TestEntity extends ContentEntityBase implements EntityPublishedInterface {

  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['terms'] = BaseFieldDefinition::create('entity_reference')
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler', 'default')
      ->setLabel(t('terms'));

    $fields += static::publishedBaseFieldDefinitions($entity_type);

    return $fields;
  }

}
