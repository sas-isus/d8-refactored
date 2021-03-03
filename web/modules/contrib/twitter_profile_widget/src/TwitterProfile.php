<?php

namespace Drupal\twitter_profile_widget;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

/**
 * Class TwitterProfile.
 *
 * @package Drupal\twitter_profile_widget
 */
class TwitterProfile implements TwitterProfileInterface {

  const BASE_URI = 'https://api.twitter.com/1.1';

  /**
   * Pull tweets from the Twitter API.
   *
   * @param array $instance
   *   All the data for the given Twitter widget.
   *
   * @return str[]
   *   An array of Twitter objects.
   */
  public static function pull(array $instance) {

    if (!\Drupal::state()->get('twitter_api_access_token')) {
      \Drupal::logger('twitter_profile_widget')->error('No access token was found for the Twitter API. Check /admin/config/media/twitter_profile_widget.');
      return FALSE;
    }
    $query = self::buildQuery(
      $instance['account'],
      $instance['list_type'],
      $instance['timeline'],
      $instance['search'],
      $instance['replies'],
      $instance['retweets']
    );

    $data_array = self::request($query);
    $json = json_encode($data_array);
    $tweets = json_decode($json);
    if (empty($tweets)) {
      return FALSE;
    }
    elseif (isset($tweets->errors)) {
      \Drupal::logger('twitter_profile_widget')->error($tweets->errors[0]->message);
      return FALSE;
    }
    elseif (isset($tweets->statuses)) {
      // The "Search" API returns statuses within the "statuses" element.
      // See https://dev.twitter.com/rest/reference/get/search/tweets .
      return $tweets->statuses;
    }
    return $tweets;
  }

  /**
   * Build the full REST URL, depending on user-selected type.
   *
   * @return string
   *   A URL, e.g., https://api.twitter.com/1.1/favorites/list.json?count=2&screen_name=episod
   */
  protected static function buildQuery($account, $type = '', $timeline = '', $search = '', $replies = 1, $retweets = 1) {
    switch ($type) {

      case 'timeline':
        $list_id = '';
        $lists = self::request('/lists/list.json?screen_name=' . $account);
        foreach ($lists as $list) {
          \Drupal::logger('twitter_profile_widget')->notice(serialize($list));
          if ($list['name'] === $timeline) {
            $list_id = $list['id'];
          }
        }
        $url = '/lists/statuses.json';
        $params = [
          'count' => 10,
          'list_id' => $list_id,
          'owner_screen_name' => $account,
          'include_rts' => $retweets,
        ];

        if ($replies == 0) {
          $params['exclude_replies'] = 1;
        }
        break;

      case 'favorites':
        $url = '/favorites/list.json';
        $params = [
          'count' => 10,
          'screen_name' => $account,
        ];

        break;

      case 'search':
        $url = '/search/tweets.json';
        $params = [
          'q' => $search,
          'count' => 10,
        ];
        break;

      default:
        // Default to getting Tweets from a user.
        $url = '/statuses/user_timeline.json';
        $params = [
          'count' => 10,
          'screen_name' => $account,
          'include_rts' => $retweets,
        ];

        if ($replies == 0) {
          $params['exclude_replies'] = 1;
        }
        break;

    }

    $getfield = '?' . http_build_query($params);
    return $url . $getfield;
  }

  /**
   * Helper method to perform the HTTP request.
   *
   * @param string $endpoint
   *   The Twiter API endpoint.
   *
   * @return mixed
   *   The Basecamp response in PHP array format, or FALSE if failed.
   */
  public static function request($endpoint) {
    $client = new Client();
    $headers = [
      'Authorization' => 'Bearer ' . \Drupal::state()->get('twitter_api_access_token'),
      'Accept' => 'application/json',
    ];
    try {
      $response = $client->request('GET', self::BASE_URI . $endpoint, [
        'headers' => $headers,
      ]);
    }
    catch (RequestException $e) {
      if ($e->hasResponse()) {
        \Drupal::logger('twitter_profile_widget')->error(Psr7\str($e->getResponse()));
        return FALSE;
      }
    }

    return json_decode($response->getBody(), TRUE);
  }

}
