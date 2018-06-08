<?php

namespace Drupal\permissions_by_entity\Event;

/**
 * Class PermissionsByEntity.
 *
 * @package Drupal\permissions_by_entity\Event
 */
class PermissionsByEntityEvents {

  /**
   * Entity Field Value Access Denied event.
   *
   * This event occurs when the access to a referenced
   * fieldable entity is denied for a user.
   *
   * @Event('Drupal/booking/Event/CreateTourbookBookingIframeUrlEvent')
   */
  const ENTITY_FIELD_VALUE_ACCESS_DENIED_EVENT = 'permissions_by_entity.entity_field_value_access_denied_event';

}
