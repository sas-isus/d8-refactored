<?php

namespace Drupal\permissions_by_entity\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\permissions_by_entity\Event\EntityFieldValueAccessDeniedEvent;
use Drupal\permissions_by_entity\Event\PermissionsByEntityEvents;
use Drupal\permissions_by_term\Service\AccessCheck;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class AccessChecker.
 *
 * @package Drupal\permissions_by_entity\Service
 */
class AccessChecker extends AccessCheck implements AccessCheckerInterface {

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  private $eventDispatcher;

  /**
   * The cache for checked entities.
   *
   * @var \Drupal\permissions_by_entity\Service\CheckedEntityCache
   */
  private $checkedEntityCache;

  /**
   * The entity field value access denied event.
   *
   * @var \Drupal\permissions_by_entity\Event\EntityFieldValueAccessDeniedEvent
   */
  private $event;

  /**
   * AccessChecker constructor.
   *
   * We override the constructor, because we do not need the entity manager.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\permissions_by_entity\Service\CheckedEntityCache $checked_entity_cache
   *   The cache for checked entities.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The core entity type manager.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(
    EventDispatcherInterface $event_dispatcher,
    CheckedEntityCache $checked_entity_cache,
    EntityManagerInterface $entity_manager,
    Connection $database
  ) {
    parent::__construct($database, $event_dispatcher);
    $this->eventDispatcher = $event_dispatcher;
    $this->checkedEntityCache = $checked_entity_cache;

    $this->event = new EntityFieldValueAccessDeniedEvent();
  }

  /**
   * {@inheritdoc}
   */
  public function isAccessAllowed(FieldableEntityInterface $entity, $uid = FALSE) {
    // Iterate over the fields the entity contains.
    foreach ($entity->getFields() as $field) {

      // We only need to check for entity reference fields
      // which references to a taxonomy term.
      if (
        $field->getFieldDefinition()->getType() == 'entity_reference' &&
        $field->getFieldDefinition()->getSetting('target_type') == 'taxonomy_term'
      ) {

        // Iterate over each referenced taxonomy term.
        /** @var \Drupal\Core\Field\FieldItemInterface $item */
        foreach ($field->getValue() as $item) {
          // Let "Permissions By Term" do the actual check.
          if (
            !empty($item['target_id']) &&
            !$this->isAccessAllowedByDatabase($item['target_id'], $uid, $entity->language()->getId())
          ) {
            // Return that the user is not allowed to access this entity.
            return FALSE;
          }
        }
      }

      // Check if the field contains another fieldable entity,
      // that we need to check.
      if ($field->entity && $field->entity instanceof FieldableEntityInterface) {

        // We need to iterate over the entities.
        $num_values = $field->count();
        if ($num_values > 0) {

          // Iterate over the field values.
          for ($i = 0; $i < $num_values; $i++) {

            // Get the value of the current field index.
            $field_value = $field->get($i);

            // If the value is null or empty we continue with the next index of
            // the loop.
            if (!$field_value) {
              continue;
            }

            // Get the field entity.
            $field_entity = $field_value->entity;

            // If the field entity is null we also continue with the next index
            // of the loop.
            if (!$field_entity) {
              continue;
            }

            // It is possible, that the referenced field entity creates a
            // circular dependency to the current entity. This will cause
            // memory limit exhausted errors because there is no way out for
            // the script. To avoid this, we need to be able to imagine if we
            // already checked this field entity before. If so, we ignore this
            // field entity, if not we can securely do a recursive call.
            //
            // Using own method to avoid "max nesting level error" trying to
            // check if the field entity is stored in the entitiesChecked array.
            if ($this->checkedEntityCache->isChecked($field_entity)) {
              continue;
            }
            else {
              // Add the current entity to the list of checked entities.
              $this->checkedEntityCache->add($field_entity);
            }

            // Do a recursive call to check if the user is allowed to access
            // this entity.
            if (!$this->isAccessAllowed($field_entity, $uid)) {

              // Dispatch an event to allow subscribers
              // to do something in this case.
              $this->event->setIndex($i);
              $this->event->setField($field);
              $this->event->setEntity($field_entity);
              $this->event->setUid($uid);

              $this->eventDispatcher
                ->dispatch(
                  PermissionsByEntityEvents::ENTITY_FIELD_VALUE_ACCESS_DENIED_EVENT,
                  $this->event
                );
              $i = $this->event->getIndex();
            }
          }
        }
      }
    }

    return TRUE;
  }

}
