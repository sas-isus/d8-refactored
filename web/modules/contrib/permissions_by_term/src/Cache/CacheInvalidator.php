<?php

namespace Drupal\permissions_by_term\Cache;

use Drupal\Core\Cache\Cache;

class CacheInvalidator {

  /**
   * @var Cache
   */
  private $cache;

  public function __construct(Cache $cache) {
    $this->cache = $cache;
  }

  public function invalidate(): void {
    $this->cache::invalidateTags([
      'search_index:node_search',
      'permissions_by_term:access_result_cache',
      'permissions_by_term:key_value_cache'
    ]);
  }

}
