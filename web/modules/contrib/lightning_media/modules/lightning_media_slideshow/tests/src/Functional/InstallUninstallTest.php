<?php

namespace Drupal\Tests\lightning_media_slideshow\Functional;

use Drupal\block_content\Entity\BlockContent;
use Drupal\field\Entity\FieldConfig;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests install/uninstall operations of Media Slideshow.
 *
 * @group lightning_media
 * @group lightning_media_slideshow
 */
class InstallUninstallTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   *
   * Slick Entity Reference has a schema error.
   *
   * @todo Remove when depending on slick_entityreference 1.2 or later.
   */
  protected static $configSchemaCheckerExclusions = [
    'core.entity_view_display.block_content.media_slideshow.default',
  ];

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'lightning_media_slideshow',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->moduleInstaller = $this->container->get('module_installer');
  }

  /**
   * The module installer service.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  private $moduleInstaller;

  /**
   * Tests module install.
   */
  public function testInstall() {
    $this->moduleInstaller->install(['lightning_media_image']);

    $this->assertEquals(['image'], $this->getAllowedMediaTypes());

    $this->moduleInstaller->install(['lightning_media_document']);
    $this->assertEquals(['document', 'image'], $this->getAllowedMediaTypes());

    $this->moduleInstaller->uninstall(['lightning_media_image']);
    $this->assertEquals(['document', 'image'], $this->getAllowedMediaTypes());
  }

  /**
   * Returns the media types which can be referenced in slideshows.
   *
   * The returned array is sorted so that it can be compared with == (i.e.,
   * ::assertEquals()), which treats the compared arrays as associative, even
   * if they're numerically indexed.
   *
   * @return string[]
   *   The reference-able media type IDs.
   */
  private function getAllowedMediaTypes() {
    $handler_settings = FieldConfig::loadByName('block_content', 'media_slideshow', 'field_slideshow_items')
      ->getSetting('handler_settings');

    $target_bundles = array_values($handler_settings['target_bundles']);
    $this->assertTrue(sort($target_bundles));

    return $target_bundles;
  }

  /**
   * Tests module uninstall.
   */
  public function testUninstall() {
    // No slideshow blocks exist yet, so validation should succeed.
    $problems = $this->moduleInstaller->validateUninstall(['lightning_media_slideshow']);
    $this->assertEmpty($problems);

    // Validation should fail if a slideshow exists.
    $slideshow_block = BlockContent::create([
      'type' => 'media_slideshow',
      'name' => $this->randomString(),
    ]);
    $slideshow_block->save();
    $problems = $this->moduleInstaller->validateUninstall(['lightning_media_slideshow']);
    $this->assertEquals(['To uninstall Media Slideshow, you must delete all slideshow blocks first.'], $problems['lightning_media_slideshow']);

    // Validation should succeed once the slideshow is deleted.
    $slideshow_block->delete();
    $problems = $this->moduleInstaller->validateUninstall(['lightning_media_slideshow']);
    $this->assertEmpty($problems);
    $module_data = $this->container->get('extension.list.module')
      ->reset()
      ->get('lightning_media_slideshow');
    $this->assertArrayNotHasKey('required', $module_data->info);
  }

}
