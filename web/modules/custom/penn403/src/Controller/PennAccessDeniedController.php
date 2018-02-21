<?php

namespace Drupal\penn403\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Cookie;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

class PennAccessDeniedController extends ControllerBase {

  protected $system_site_config;

  public static function create(ContainerInterface $container) {
    $config_factory = $container->get('config.factory');

    return new static($config_factory);
  }

  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->system_site_config = $config_factory->get('system.site');
  }

  public function on403 () {
    $element = array(
      '#markup' => $this->getMarkupForAccessLevel(),
    );

    return $element;
  }

  private function getMarkupForAccessLevel() {
    $config = $this->config('penn403.settings');
    $authorized_roles = $config->get('authorized_roles');

    $current_user = \Drupal::currentUser();
    $user_roles = $current_user->getRoles();

    $matching_roles = array_intersect($authorized_roles, $user_roles);
    $is_authorized = sizeof($matching_roles) > 0;

    $returned_markup = NULL;

    if ($current_user->isAnonymous()) {
      // is auto_redirect on? if so, redirect with destination query
      if ($config->get('auto_redirect') == 1) {
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

            $response = new RedirectResponse($login_path->toString());

            $cookie = new Cookie('simplesamlphp_auth_returnto', $current_url->toString(), time() + (60 * 60));
            $response->headers->setCookie($cookie);

            $response->send();
          } catch (\Exception $e) {
            // Drupal throws an exception if you try to get a non-existent route,
            // so we trap it here and return an empty string to hide the block.
            return '';
          }
        }
      } else {
        // if not, return 'login required' page
        $returned_markup = $this->getLoginRequiredContent();
      }
    } elseif (!$is_authorized) {
      // if user is authenticated, but lacking an authorized roles,
      // return 'insufficient privileges' page
      $returned_markup = $this->getInsufficientPrivilegesContent();
    }

    return $returned_markup;
  }

  private function getLoginRequiredContent() {
    $config = $this->config('penn403.settings');
    $login_route = $config->get('auth_login_route');
    $route_provider = \Drupal::service('router.route_provider');

    $current_uri = \Drupal::request()->getRequestUri();
    $current_url = Url::fromUserInput($current_uri, ['absolute' => FALSE]);
    $options = ['query' => ['destination' => $current_url->toString()]];

    try {
      $login_route = $route_provider->getRouteByName($login_route);
      $link_url = Url::fromRoute($login_route, [], $options);
    } catch (\Exception $e) {
      $link_url = Url::fromUserInput('/saml_login', [], $options);
    }

    $link = Link::fromTextAndUrl($this->t('Log in using your PennKey to access this content.'), $link_url);

    $link = $link->toString();

    $output = '';
    $output .= '<div class="penn403-login">';
    $output .= '<h2>' . $this->t('Login Required') . '</h2>';
    $output .= $link;
    $output .= '</div>';

    return $output;
  }

  private function getInsufficientPrivilegesContent() {
    $config = $this->config('penn403.settings');
    $contact_email = $config->get('access_contact');

    if (!$contact_email || $contact_email === '') {
      $contact_email = $this->system_site_config->get('mail');
    }

    $mail_url = Url::fromUri('mailto:' . $contact_email);
    $link_options = array(
      'attributes' => array(
        'rel' => 'nofollow',
      ),
    );
    $mail_url->setOptions($link_options);

    $mail_link = Link::fromTextAndUrl($this->t('contact the site administrator'), $mail_url);
    $output = '';
    $output .= '<div class="penn403-insufficient-privileges">';
    $output .= '<h2>' . $this->t('Insufficient Privileges') . '</h2>';
    $output .= $this->t('You do not have sufficient privileges to access this page. ');
    $output .= $this->t('Please') . ' ' . $mail_link->toString() . ' ' . $this->t('to request access.');
    $output .= '</div>';
    return $output;
  }
}
