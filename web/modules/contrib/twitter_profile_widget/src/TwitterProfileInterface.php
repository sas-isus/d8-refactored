<?php

namespace Drupal\twitter_profile_widget;

/**
 * Interface TwitterProfileInterface.
 *
 * @package Drupal\twitter_profile_widget
 */
interface TwitterProfileInterface {

  /**
   * Pull tweets from the Twitter API.
   *
   * @param array $instance
   *   All the data for the given Twitter widget.
   *
   * @return str[]
   *   An array of Twitter objects.
   */
  public static function pull(array $instance);

}
