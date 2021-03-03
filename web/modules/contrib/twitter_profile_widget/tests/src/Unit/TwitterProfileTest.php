<?php

namespace Drupal\Tests\twitter_profile_widget\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\twitter_profile_widget\TwitterProfile;

/**
 * Tests the "TwitterProfile" service, which builds Twitter API queries.
 *
 * @coversDefaultClass \Drupal\twitter_profile_widget\TwitterProfile
 * @group twitter_profile_widget
 *
 * @see Drupal\twitter_profile_widget\TwitterProfile
 */
class TwitterProfileTest extends UnitTestCase {

  /**
   * Get an accessible method using reflection.
   */
  public function getAccessibleMethod($class_name, $method_name) {
    $class = new \ReflectionClass($class_name);
    $method = $class->getMethod($method_name);
    $method->setAccessible(TRUE);
    return $method;
  }

  /**
   * Test TwitterProfile::buildQuery().
   *
   * Test that an expected Twitter REST URL for the twitter timeline returns.
   * Since buildQuery() is a protected method, alter the class using reflection.
   *
   * @dataProvider queryDataProvider
   */
  public function testQuery($config, $expected) {
    // Get a reflected, accessible version of the buildQuery() method.
    $protected_method = $this->getAccessibleMethod(
      'Drupal\twitter_profile_widget\TwitterProfile',
      'buildQuery'
    );
    // Create a new TwitterProfile object.
    $pp = new TwitterProfile();
    // Use the reflection to invoke on the object.
    $result = $protected_method->invokeArgs($pp, [
      $config['account'],
      $config['type'],
      $config['timeline'],
      $config['search'],
      $config['replies'],
      $config['retweets'],
    ]);
    // Make an assertion.
    $this->assertEquals($expected['url'], $result['url']);
    $this->assertEquals($expected['getfield'], $result['getfield']);
  }

  /**
   * Data provider for testQuery().
   */
  public function queryDataProvider() {
    return [
      [
        [
          'account' => 'testuser',
          'type' => 'timeline',
          'timeline' => 'mytimeline',
          'search' => 'search param',
          'replies' => 1,
          'retweets' => 1,
        ],
        [
          'url' => 'https://api.twitter.com/1.1/lists/statuses.json',
          'getfield' => '?count=10&slug=mytimeline&owner_screen_name=testuser&include_rts=1',
        ],
      ],
      [
        [
          'account' => 'testuser',
          'type' => 'timeline',
          'timeline' => 'mytimeline',
          'search' => 'search param',
          'replies' => 0,
          'retweets' => 0,
        ],
        [
          'url' => 'https://api.twitter.com/1.1/lists/statuses.json',
          'getfield' => '?count=10&slug=mytimeline&owner_screen_name=testuser&include_rts=0&exclude_replies=1',
        ],
      ],
      [
        [
          'account' => 'testuser',
          'type' => 'search',
          'timeline' => 'mytimeline',
          'search' => 'search param',
          'replies' => 1,
          'retweets' => 1,
        ],
        [
          'url' => 'https://api.twitter.com/1.1/search/tweets.json',
          'getfield' => '?q=search+param&count=10',
        ],
      ],
      [
        [
          'account' => 'testuser',
          'type' => 'search',
          'timeline' => 'mytimeline',
          'search' => '#search . param%',
          'replies' => 1,
          'retweets' => 1,
        ],
        [
          'url' => 'https://api.twitter.com/1.1/search/tweets.json',
          'getfield' => '?q=%23search+.+param%25&count=10',
        ],
      ],
      [
        [
          'account' => 'testuser',
          'type' => 'favorites',
          'timeline' => 'mytimeline',
          'search' => 'search param',
          'replies' => 1,
          'retweets' => 1,
        ],
        [
          'url' => 'https://api.twitter.com/1.1/favorites/list.json',
          'getfield' => '?count=10&screen_name=testuser',
        ],
      ],
      [
        [
          'account' => 'testuser',
          'type' => 'status',
          'timeline' => 'mytimeline',
          'search' => 'search param',
          'replies' => 1,
          'retweets' => 1,
        ],
        [
          'url' => 'https://api.twitter.com/1.1/statuses/user_timeline.json',
          'getfield' => '?count=10&screen_name=testuser&include_rts=1',
        ],
      ],
      [
        [
          'account' => 'testuser',
          'type' => 'status',
          'timeline' => 'mytimeline',
          'search' => 'search param',
          'replies' => 0,
          'retweets' => 0,
        ],
        [
          'url' => 'https://api.twitter.com/1.1/statuses/user_timeline.json',
          'getfield' => '?count=10&screen_name=testuser&include_rts=0&exclude_replies=1',
        ],
      ],
    ];
  }

}
