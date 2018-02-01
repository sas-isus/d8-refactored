<?php

namespace Drupal\gathercontent;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class for querying metatag data.
 */
class MetatagQuery implements ContainerInjectionInterface {

  protected $entityFieldManager;

  /**
   * MetatagQuery constructor.
   */
  public function __construct(EntityFieldManagerInterface $entityFieldManager) {
    $this->entityFieldManager = $entityFieldManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_field.manager')
    );
  }

  /**
   * Check if content type has any metatag fields.
   *
   * @param string $content_type
   *   Machine name of content type.
   *
   * @return bool
   *   TRUE if metatag field exists.
   */
  public function checkMetatag($content_type) {
    $instances = $this->entityFieldManager
      ->getFieldDefinitions('node', $content_type);
    foreach ($instances as $name => $instance) {
      /** @var \Drupal\Core\Field\FieldDefinitionInterface $instance */
      if ($instance->getType() === 'metatag') {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Get list of metatag fields.
   *
   * @param string $content_type
   *   Machine name of content type.
   *
   * @return array
   *   Array of metatag fields.
   */
  public function getMetatagFields($content_type) {
    $instances = $this->entityFieldManager
      ->getFieldDefinitions('node', $content_type);
    $fields = [];
    foreach ($instances as $name => $instance) {
      /** @var \Drupal\Core\Field\FieldDefinitionInterface $instance */
      if ($instance->getType() === 'metatag') {
        $fields[] = $instance->getName();
      }
    }
    return $fields;
  }

}
