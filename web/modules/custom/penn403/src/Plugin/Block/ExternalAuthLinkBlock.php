<?php

namespace Drupal\penn403\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Provides an external auth login link with a redirect destination
 *
 * @Block(
 *   id = "externalauthlinkblock",
 *   admin_label = @Translation("External Auth Link Block"),
 *   category = @Translation("Penn 403"),
 * )
 */
class ExternalAuthLinkBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_user = \Drupal::currentUser();

    // If user is already logged in, don't show link
    if ($current_user->isAnonymous() !== TRUE) return null;

    $config = \Drupal::config('penn403.settings');

    $login_route = NULL;
    $login_path = NULL;

    // Get path to external login page
    $login_route = $config->get('auth_login_route');

    if ($login_route && $login_route !== '') {
      $route_provider = \Drupal::service('router.route_provider');

      try {
        $current_uri = \Drupal::request()->getRequestUri();
        $current_url = Url::fromUserInput($current_uri, ['absolute' => FALSE]);
        $options = ['query' => ['destination' => $current_url->toString()]];

        $login_route = $route_provider->getRouteByName($login_route);
        $login_path = Url::fromRoute($login_route, [], $options);
        $link = Link::fromTextAndUrl($this->t('Log in'), $login_path);
        $link = $link->toString();
      } catch (\Exception $e) {
        // Drupal throws an exception if you try to get a non-existent route,
        // so we trap it here and return an empty string to hide the block.
        return '';
      }
    }

    return array(
      '#markup' => $link,
    );
  }
}
