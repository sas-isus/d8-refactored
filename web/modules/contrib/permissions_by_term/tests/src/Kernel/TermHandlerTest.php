<?php

namespace Drupal\Tests\permissions_by_term\Kernel;

use Drupal\permissions_by_term\Service\TermHandler;


/**
 * @group permissions_by_term
 */
class TermHandlerTest extends PBTKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
  }

  public function testGetTidsBoundForAllNids(): void {
    $this->createRelationOneGrantedTerm();
    $this->createRelationOneGrantedTerm();

    /**
     * @var TermHandler $termHandler
     */
    $termHandler = \Drupal::service('permissions_by_term.term_handler');
    $tidsBoundToAllNids = $termHandler->getTidsBoundToAllNids();

    $expectedNidToTidsPairs = array (
      1 =>
        array (
          0 => '1',
          1 => '2',
          2 => '3',
        ),
      2 =>
        array (
          0 => '4',
          1 => '5',
          2 => '6',
        ),
    );

    self::assertArraySubset($expectedNidToTidsPairs, $tidsBoundToAllNids);
  }

}
