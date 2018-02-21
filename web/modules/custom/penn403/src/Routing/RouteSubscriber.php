<?php
/**
 * @file
 * Contains \Drupal\penn403\Routing\RouteSubscriber.
 */

namespace Drupal\penn403\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;
/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {
  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('system.403')) {
      $route->setDefault('_controller', '\Drupal\penn403\Controller\PennAccessDeniedController:on403');
    }
  }
}
