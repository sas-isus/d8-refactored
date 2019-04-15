<?php

namespace Drupal\permissions_by_term\KeyValueCache;


class CacheNegotiator implements CacheInterface {

  /**
   * @var \Drupal\permissions_by_term\KeyValueCache\SharedTempStore
   */
  private $sharedTempStore;

  /**
   * @var \Drupal\permissions_by_term\KeyValueCache\StaticStorage
   */
  private $staticStorage;

  public function __construct(SharedTempStore $sharedTempStore, StaticStorage $staticStorage) {
    $this->sharedTempStore = $sharedTempStore;
    $this->staticStorage = $staticStorage;
  }

  public function get(string $namespace): ?array {
    if ($this->staticStorage->has($namespace)) {
      return $this->staticStorage->get($namespace);
    }

    if ($this->sharedTempStore->has($namespace)) {
      return $this->sharedTempStore->get($namespace);
    }

    return null;
  }

  public function set(string $namespace, array $data): void {
    $this->staticStorage->set($namespace, $data);
    $this->sharedTempStore->set($namespace, $data);
  }

  public function has(string $namespace): bool {
    if ($this->staticStorage->has($namespace)) {
      return TRUE;
    }

    if ($this->sharedTempStore->has($namespace)) {
      return TRUE;
    }

    return FALSE;
  }

  public function clear(string $namespace): void {
    if ($this->staticStorage->has($namespace)) {
      $this->staticStorage->clear($namespace);
    }

    if ($this->sharedTempStore->has($namespace)) {
      $this->sharedTempStore->clear($namespace);
    }
  }

}
