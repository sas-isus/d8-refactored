<?php

namespace Drupal\permissions_by_term\Entity\Config;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the settings entity.
 *
 * The lines below, starting with '@ConfigEntityType,' are a plugin annotation.
 * These define the entity type to the entity type manager.
 *
 * The properties in the annotation are as follows:
 *  - id: The machine name of the entity type.
 *  - label: The human-readable label of the entity type. We pass this through
 *    the "@Translation" wrapper so that the multilingual system may
 *    translate it in the user interface.
 *  - handlers: An array of entity handler classes, keyed by handler type.
 *    - access: The class that is used for access checks.
 *    - list_builder: The class that provides listings of the entity.
 *    - form: An array of entity form classes keyed by their operation.
 *  - entity_keys: Specifies the class properties in which unique keys are
 *    stored for this entity type. Unique keys are properties which you know
 *    will be unique, and which the entity manager can use as unique in database
 *    queries.
 *  - links: entity URL definitions. These are mostly used for Field UI.
 *    Arbitrary keys can set here. For example, User sets cancel-form, while
 *    Node uses delete-form.
 *
 * @see http://previousnext.com.au/blog/understanding-drupal-8s-config-entities
 * @see annotation
 * @see Drupal\Core\Annotation\Translation
 *
 * @ingroup permissions_by_term
 *
 * @ConfigEntityType(
 *   id = "settings",
 *   label = @Translation("Settings"),
 *   entity_keys = {
 *     "id" = "id",
 *     "value" = "value"
 *   }
 * )
 */
class Settings extends ConfigEntityBase {

  /**
   * @var string
   */
  public $id;

  /**
   * @var bool
   */
  public $value;

}
