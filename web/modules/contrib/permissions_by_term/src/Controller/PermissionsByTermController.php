<?php

namespace Drupal\permissions_by_term\Controller;

use Drupal\Component\Utility\Tags;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Default controller for the permissions_by_term module.
 */
class PermissionsByTermController extends ControllerBase {

  /**
   * Returns JSON response for user's autocomplete field in permissions form.
   *
   * @return JsonResponse
   *   The response as JSON.
   */
  public function autoCompleteMultiple() {
    // The user enters a comma-separated list of users.
    // We only autocomplete the last user.
    $array = Tags::explode($_REQUEST['q']);

    // Fetch last user.
    $last_string = trim(array_pop($array));

    $matches = [];

    $aUserIds = \Drupal::entityQuery('user')
      ->condition('name', $last_string, 'CONTAINS')
      ->execute();

    $prefix = count($array) ? implode(', ', $array) . ', ' : '';

    foreach ($aUserIds as $iUserId) {
      $oUser = user_load($iUserId);
      $matches[$prefix . $oUser->getDisplayName()] = $oUser->getDisplayName();
    }

    return new JsonResponse($matches);
  }

}
