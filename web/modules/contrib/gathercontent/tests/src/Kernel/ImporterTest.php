<?php

namespace Drupal\Tests\gathercontent\Kernel;

use Drupal\gathercontent\Entity\Operation;
use Drupal\gathercontent\Import\ImportOptions;
use Drupal\gathercontent\Import\NodeUpdateMethod;
use Drupal\gathercontent_test\MockData;
use Drupal\gathercontent_test\MockDrupalGatherContentClient;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\gathercontent_test\EventSubscriber\MockGcEventSubscriber;

/**
 * Tests for the importer class.
 *
 * - menu creation.
 * - import events.
 * - status update.
 *
 * @group gathercontent
 */
class ImporterTest extends GcImportTestBase {

  /**
   * Test the import function.
   */
  public function testImport() {
    $importer = static::getImporter();
    $operation = Operation::create([
      'type' => 'import',
    ]);
    $importOptions = new ImportOptions(
      NodeUpdateMethod::ALWAYS_CREATE,
      FALSE,
      2,
      'main:',
      $operation->uuid()
    );
    $mapping = MockData::getMapping();
    $item = MockData::createItem(
      $mapping,
      [FALSE, FALSE, FALSE],
      [FALSE, FALSE, FALSE]
    );
    $importer->import($item, $importOptions);
    static::assertStatusChooseCalled($importOptions->getNewStatus());
    static::assertMockImportEventsCalled();
    static::assertMenuLinkCreated('main', $item->name);
  }

  /**
   * Assert import's events are called.
   */
  public static function assertMockImportEventsCalled() {
    static::assertEquals(
      1,
      MockGcEventSubscriber::$postNodeSaveCalled,
      'The post node save event should only be dispatched once.'
    );
    static::assertEquals(
      1,
      MockGcEventSubscriber::$preNodeSaveCalled,
      'The pre node save event should only be dispatched once.'
    );
  }

  /**
   * Assert status changed.
   */
  public static function assertStatusChooseCalled($statusId) {
    static::assertEquals(
      MockDrupalGatherContentClient::$choosenStatus,
      $statusId,
      'Wrong status was sent to the client.'
    );
  }

  /**
   * Assert menu link creation.
   */
  public static function assertMenuLinkCreated($parentMenuName, $menuTitle) {
    $menus = MenuLinkContent::loadMultiple();
    $menusInParent = array_filter($menus, function ($menu) use ($parentMenuName, $menuTitle) {
      /** @var \Drupal\menu_link_content\Entity\MenuLinkContent $menu */
      $isSameParentMenu = $menu->getMenuName() === $parentMenuName;
      $isSameMenuName = $menu->getTitle() === $menuTitle;
      return $isSameMenuName && $isSameParentMenu;
    });
    static::assertEquals(
      1,
      count($menusInParent),
      "Didn't find '$menuTitle' in '$parentMenuName' parent menu."
    );
  }

}
