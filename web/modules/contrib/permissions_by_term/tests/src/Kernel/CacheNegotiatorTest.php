<?php

namespace Drupal\Tests\permissions_by_term\Kernel;

use Drupal\permissions_by_term\Model\NidToTidsModel;


/**
 * Class AccessCheckTest
 *
 * @package Drupal\Tests\permissions_by_term\Kernel
 * @group permissions_by_term
 */
class CacheNegotiatorTest extends PBTKernelTestBase {

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
     * @var \Drupal\permissions_by_term\KeyValueCache\CacheNegotiator $cacheNegotiator
     */
    $cacheNegotiator = \Drupal::service('permissions_by_term.cache_negotiator');

    self::assertFalse($cacheNegotiator->has(NidToTidsModel::class));

    $cacheNegotiator->set(NidToTidsModel::class, $data);

    self::assertTrue($cacheNegotiator->has(NidToTidsModel::class));

    self::assertCount(1, $cacheNegotiator->get(NidToTidsModel::class));

    $cacheNegotiator->clear(NidToTidsModel::class);

    self::assertNull($cacheNegotiator->get(NidToTidsModel::class));
  }

}
