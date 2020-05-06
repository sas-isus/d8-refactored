<?php

namespace Drupal\permissions_by_term\Cache;


use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;


class KeyValueCache {

  /**
   * The default cache bin.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  public function __construct(CacheBackendInterface $cache) {
    $this->cache = $cache;
  }

  public function set(array $data): void {
    $cid = 'permissions_by_term:key_value_cache';

    $tags = [
      'permissions_by_term:key_value_cache',
    ];

    $tags = Cache::mergeTags($tags, [$cid]);

    $this->cache->set($cid, $data, Cache::PERMANENT, $tags);

    $staticCache = &drupal_static(__FUNCTION__ . $cid, NULL);
    $staticCache = $data;
  }

  public function get(): array {
    $cid = 'permissions_by_term:key_value_cache';

    $staticCache = &drupal_static(__FUNCTION__ . $cid, NULL);

    if ($staticCache) {
      return $staticCache;
    }

    $result = $this->cache->get($cid);

    $data = $result->data;

    if (!is_array($data)) {
      throw new \Exception('Result from cache was not an array.');
    }

    return $data;
  }

  public function has(): bool {
    $cid = 'permissions_by_term:key_value_cache';

    $staticCache = &drupal_static(__FUNCTION__ . $cid, NULL);

    if ($staticCache) {
      $data = $staticCache;

      if (!is_array($data)) {
        return FALSE;
      }

      return TRUE;
    }

    $result = $this->cache->get($cid);

    if (!isset($result->data)) {
      return FALSE;
    }

    $data = $result->data;

    if (!is_array($data)) {
      return FALSE;
    }

    return TRUE;
  }

}
