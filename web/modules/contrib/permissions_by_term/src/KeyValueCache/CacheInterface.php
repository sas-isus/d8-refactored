<?php

namespace Drupal\permissions_by_term\KeyValueCache;


interface CacheInterface {

  public function get(string $namespace): ?array;

  public function set(string $namespace, array $data): void;

  public function has(string $namespace): bool;

  public function clear(string $namespace): void;

}
