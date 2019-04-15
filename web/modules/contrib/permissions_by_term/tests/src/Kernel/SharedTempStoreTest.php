<?php

namespace Drupal\Tests\permissions_by_term\Kernel;

use Drupal\permissions_by_term\Model\NidToTidsModel;


/**
 * @package Drupal\Tests\permissions_by_term\Kernel
 * @group permissions_by_term
 */
class SharedTempStoreTest extends PBTKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
  }

  public function nidToTidsModelProvider() {
    return [
      [
        [new NidToTidsModel()]
      ],
    ];
  }

  /**
   * @dataProvider nidToTidsModelProvider
   */
  public function testGet(array $data) {
    /**
     * @var \Drupal\permissions_by_term\KeyValueCache\SharedTempStore $sharedTempStore
     */
    $sharedTempStore = \Drupal::service('permissions_by_term.shared_temp_store');

    self::assertFalse($sharedTempStore->has(NidToTidsModel::class));

    $sharedTempStore->set(NidToTidsModel::class, $data);

    self::assertTrue($sharedTempStore->has(NidToTidsModel::class));

    self::assertCount(1, $sharedTempStore->get(NidToTidsModel::class));

    $sharedTempStore->clear(NidToTidsModel::class);

    self::assertNull($sharedTempStore->get(NidToTidsModel::class));
  }

}
