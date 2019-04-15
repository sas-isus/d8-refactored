<?php

namespace Drupal\Tests\permissions_by_term\Kernel;

use Drupal\permissions_by_term\Model\NidToTidsModel;


/**
 * @package Drupal\Tests\permissions_by_term\Kernel
 * @group permissions_by_term
 */
class StaticStorageTest extends PBTKernelTestBase {

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
     * @var \Drupal\permissions_by_term\KeyValueCache\SharedTempStore $staticStorage
     */
    $staticStorage = \Drupal::service('permissions_by_term.static_storage');

    self::assertFalse($staticStorage->has(NidToTidsModel::class));

    $staticStorage->set(NidToTidsModel::class, $data);

    self::assertTrue($staticStorage->has(NidToTidsModel::class));

    self::assertCount(1, $staticStorage->get(NidToTidsModel::class));

    $staticStorage->clear(NidToTidsModel::class);

    self::assertNull($staticStorage->get(NidToTidsModel::class));
  }

}
