<?php

namespace Drupal\twitter_profile_widget\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\twitter_profile_widget\TwitterProfile;

/**
 * Plugin implementation of the 'twitter_widget' formatter.
 *
 * @FieldFormatter(
 *   id = "twitter_widget",
 *   label = @Translation("Twitter widget"),
 *   field_types = {
 *     "twitter_widget"
 *   }
 * )
 */
class TwitterWidgetFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode = NULL) {
    $instances = $items->getValue();
    $elements = [];
    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('twitter_profile_widget')->getPath();
    foreach ($instances as $instance) {
      $elements[] = [
        '#theme' => 'twitter_profile_widget',
        '#headline' => (string) $instance['headline'],
        '#tweets' => $this->getTweets($instance),
        '#view_all' => $this->getViewAll($instance),
        '#reply_icon' => '/' . $module_path . '/assets/reply.svg',
        '#retweet_icon' => '/' . $module_path . '/assets/retweet.svg',
        '#favorite_icon' => '/' . $module_path . '/assets/favorite.svg',
      ];
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function view(FieldItemListInterface $items, $langcode = NULL) {
    $elements = parent::view($items, $langcode);
    $elements['#cache']['tags'][] = 'twitter_profile_widget';
    return $elements;
  }

  /**
   * Helper function to prepare the "View all" link.
   */
  protected function getViewAll($instance) {
    if (!empty($instance['view_all'])) {
      if ($instance['list_type'] === 'search') {
        $params = [
          'q' => $instance['search'],
        ];
        $getfield = '?' . http_build_query($params);
        $url = Url::fromUri('https://twitter.com/search' . $getfield);
      }
      else {
        $url = Url::fromUri('https://twitter.com/' . $instance['account']);
      }
      return Link::fromTextAndUrl($instance['view_all'], $url);
    }
    return '';
  }

  /**
   * Implements hook_preprocess_HOOK().
   *
   * Take the user-entered Twitter "configuration" and return rendered tweets.
   */
  protected function getTweets($instance) {
    $tweets = TwitterProfile::pull($instance);
    // If the API call returns errors, do not send any data to the template.
    if ($tweets) {
      return $this->prepareTweets($tweets, $instance['count']);
    }
    return '';
  }

  /**
   * Helper to parse Twitter's JSON and return a normalized array of tweets.
   *
   * @param object $tweets
   *   An array of tweets, in Twitter class format.
   * @param int $count
   *   How many tweets should be displayed.
   *
   * @return string[]
   *   Non-keyed array of tweet elements.
   */
  protected function prepareTweets($tweets, $count = 5) {
    $inc = 0;
    $tweets_filtered = [];
    foreach ($tweets as $tweet) {
      $inc++;
      $tweet->retweet_eyebrow = FALSE;
      // If this is a retweet, use the API-provided sub-element.
      if (isset($tweet->retweeted_status)) {
        $id = $tweet->retweeted_status->id;
        $retweet_user = $tweet->retweeted_status->user->screen_name;
        $original_user = $tweet->user->name;
        $original_screen_name = $tweet->user->screen_name;
        $retweet_link = Url::fromUri('//twitter.com/' . $retweet_user . '/status/' . $id);
        $user_text = $original_user . ' Retweeted';
        $user_url = Url::fromUri('//twitter.com/' . $original_screen_name);
        $user_link = Link::fromTextAndUrl($user_text, $user_url);
        // Switch $tweet object to its sub-element.
        $tweet = $tweet->retweeted_status;
        // Add the retweet eyebrow.
        $tweet->retweet_link = $retweet_link;
        $tweet->retweet_user = $user_link;
      }

      // Prepare the Tweet Action links, based on $tweet->id.
      $timestamp = strtotime($tweet->created_at);
      $tweets_filtered[$inc] = [
        'id'        => (int) $tweet->id,
        'image'     => self::schemaFreeLink($tweet->user->profile_image_url),
        'image_user' => $tweet->user->name,
        'author'    => $tweet->user->name,
        'username'  => $tweet->user->screen_name,
        'text'      => self::parseTwitterLinks($tweet->text),
        'timestamp' => $timestamp,
        'time_ago' => $this->t('@time ago', ['@time' => \Drupal::service('date.formatter')->formatInterval(\Drupal::time()->getRequestTime() - $timestamp)]),
        'tweet_reply2' => Url::fromUri('//twitter.com/intent/tweet?in_reply_to=' . $tweet->id),
        'tweet_retweet' => Url::fromUri('//twitter.com/intent/retweet?tweet_id=' . $tweet->id),
        'tweet_star' => Url::fromUri('//twitter.com/intent/favorite?tweet_id=' . $tweet->id),
      ];
      if (isset($tweet->retweet_link)) {
        $tweets_filtered[$inc]['retweet_link'] = $tweet->retweet_link;
        $tweets_filtered[$inc]['retweet_user'] = $tweet->retweet_user;
      }
      if ($inc >= $count) {
        break;
      }
    }
    return $tweets_filtered;
  }

  /**
   * Strip 'http://' and 'https://' from a url, and replace it for '//'.
   */
  public static function schemaFreeLink($url) {
    $schemes = ['http://', 'https://'];
    $url = str_replace($schemes, '//', $url);
    return $url;
  }

  /**
   * Helper function.
   *
   * Parses tweet text and replaces links, at-mentions, and hashtags.
   *
   * @param string $text
   *   String with the tweet.
   *
   * @return string
   *   Converted tweet that has anchor links to Twitter entity types.
   */
  public static function parseTwitterLinks($text) {
    // Links.
    $text = preg_replace('@(https?://([-\w\.]+)+(/([\w/_\.]*(\?\S+)?(#\S+)?)?)?)@', '<a href="$1">$1</a>', $text);
    // @ mentions.
    $text = preg_replace('/@(\w+)/', '<a href="//twitter.com/$1">@$1</a>', $text);
    // Hashtags.
    $text = preg_replace('/\s#(\w+)/', ' <a href="//twitter.com/search?q=%23$1">#$1</a>', $text);
    return $text;
  }

}
