<?php

namespace Drupal\permissions_by_term\KeyValueCache;


use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\TempStore\SharedTempStoreFactory;
use Drupal\Core\TempStore\TempStoreException;

class SharedTempStore implements CacheInterface {

  /**
   * @var \Drupal\Core\TempStore\SharedTempStore
   */
  private $sharedTempStore;

  /**
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  private $logger;

  public function __construct(SharedTempStoreFactory $sharedTempStoreFactory, LoggerChannelInterface $logger) {
    $this->sharedTempStore = $sharedTempStoreFactory->get('permissions_by_term');
    $this->logger = $logger;
  }

  public function get(string $namespace): ?array {
    try {
      return $this->sharedTempStore->get($namespace);
    } catch (TempStoreException $exception) {
      $this->logger->error($exception->getMessage());
    }
  }

  public function set(string $namespace, array $data): void {
    $this->sharedTempStore->set($namespace, $data);
  }

  public function has(string $namespace): bool {
    if (empty($this->sharedTempStore->get($namespace))) {
      return FALSE;
    }

    return TRUE;
  }

  public function clear(string $namespace): void {
    $this->sharedTempStore->delete($namespace);
  }


}
