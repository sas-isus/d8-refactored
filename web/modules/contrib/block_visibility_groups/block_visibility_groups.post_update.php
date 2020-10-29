<?php

/**
 * @file
 * Block visibility groups post updates.
 */

/**
 * Grant administer block visibility groups permission.
 *
 * The "administer block visibility groups" permission replaced "administer
 * site configuration" as the administrative permission for block visibility
 * groups. Since the "administer blocks" permission is required to access the
 * related routes, grant the new permission to users with both permissions.
 */
function block_visibility_groups_post_update_grant_administer_permission() {
  $roles = \Drupal::entityTypeManager()
    ->getStorage('user_role')
    ->loadMultiple();

  /** @var \Drupal\user\RoleInterface $role */
  foreach ($roles as $role) {
    if ($role->hasPermission('administer site configuration') && $role->hasPermission('administer blocks')) {
      $role->grantPermission('administer block visibility groups');
      $role->save();
    }
  }
}
