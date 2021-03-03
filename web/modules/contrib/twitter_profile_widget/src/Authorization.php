<?php

namespace Drupal\twitter_profile_widget;

use Drupal\Core\Cache\Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

/**
 * Handle Application Only authorization.
 */
class Authorization {

  const BASE_URI = 'https://api.twitter.com';

  /**
   * Retrieve an 'Application Only' authorization token.
   *
   * @param string $key
   *   The application consumer key.
   * @param string $secret
   *   The application consumer secret.
   *
   * @return bool
   *   Whether or not an authorization token was retrieved.
   */
  public static function getToken($key, $secret) {
    $client = new Client([
      'base_uri' => self::BASE_URI,
      'auth' => [
        $key,
        $secret,
      ],
    ]);
    $options = [
      'form_params' => [
        'grant_type' => 'client_credentials',
      ],
    ];
    try {
      $response = $client->post('/oauth2/token', $options);
      $body = json_decode($response->getBody(), TRUE);
      \Drupal::state()->set('twitter_api_access_token', $body['access_token']);
      // Invalidate the cache so that potentially broken widgets now display.
      Cache::invalidateTags(['twitter_profile_widget']);
      \Drupal::logger('twitter_profile_widget')->error('Refreshed Twitter API token.');
    }
    catch (RequestException $e) {
      if ($e->hasResponse()) {
        $messenger = \Drupal::messenger();
        $messenger->addMessage($e->getResponse()->getBody()->getContents(), 'error');
        \Drupal::logger('twitter_profile_widget')->error(Psr7\str($e->getResponse()));
        return FALSE;
      }
    }
    return TRUE;
  }

}
