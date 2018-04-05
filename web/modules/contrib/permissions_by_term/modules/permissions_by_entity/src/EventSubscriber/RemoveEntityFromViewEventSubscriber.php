<?php

namespace Drupal\permissions_by_entity\EventSubscriber;

use Drupal\permissions_by_entity\Event\EntityFieldValueAccessDeniedEvent;
use Drupal\permissions_by_entity\Event\PermissionsByEntityEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class RemoveEntityFromViewEventSubscriber.
 *
 * @package Drupal\permissions_by_entity\EventSubscriber
 */
class RemoveEntityFromViewEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      PermissionsByEntityEvents::ENTITY_FIELD_VALUE_ACCESS_DENIED_EVENT =>
      [
        'onEntityFieldAccessDenied',
      ],
    ];
  }

  /**
   * Callback method.
   *
   * Callback method that will be called when the
   * ENTITY_FIELD_VALUE_ACCESS_DENIED_EVENT has been triggered.
   *
   * @param \Drupal\permissions_by_entity\Event\EntityFieldValueAccessDeniedEvent $event
   *   The event.
   */
  public function onEntityFieldAccessDenied(EntityFieldValueAccessDeniedEvent $event) {
    // Get the field.
    $field = $event->getField();

    // Get the number of values this field contains.
    $num_values = $field->count();

    // Get the current value of the field.
    $field_values = $field->getValue();

    // Iterate over the values.
    for ($i = 0; $i < $num_values; $i++) {
      $field_entity = $field->get($i)->entity;

      // If the entity matches the entity of the event.
      if ($field_entity === $event->getEntity()) {
        // Remove the this value from the values array.
        unset($field_values[$i]);

        // We need to decrement the current index.
        $event->setIndex($event->getIndex() - 1);
      }
    }

    // Set the field values.
    $field->setValue($field_values);
  }

}
