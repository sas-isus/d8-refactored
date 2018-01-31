<?php

namespace Drupal\gathercontent;

use Cheppers\GatherContent\DataTypes\Item;
use Drupal\gathercontent\Entity\Mapping;

/**
 * A static class to return and cache mapping entities.
 */
class MappingLoader {

  protected static $mappings = [];

  /**
   * Cache mappings when loading them.
   */
  public static function load(Item $gcItem) {
    if (!isset(static::$mappings[$gcItem->id])) {
      static::$mappings[$gcItem->id] = static::getMapping($gcItem);
    }
    return static::$mappings[$gcItem->id];
  }

  /**
   * Return the mapping associated with the given Item.
   */
  public static function getMapping(Item $gcItem) {
    $mappingId = \Drupal::entityQuery('gathercontent_mapping')
      ->condition('gathercontent_project_id', $gcItem->projectId)
      ->condition('gathercontent_template_id', $gcItem->templateId)
      ->execute();

    if (empty($mappingId)) {
      throw new \Exception("Operation failed: Template not mapped.");
    }

    $mappingId = reset($mappingId);
    $mapping = Mapping::load($mappingId);

    if ($mapping === NULL) {
      throw new \Exception("No mapping found with id: $mappingId");
    }

    return $mapping;
  }

}
