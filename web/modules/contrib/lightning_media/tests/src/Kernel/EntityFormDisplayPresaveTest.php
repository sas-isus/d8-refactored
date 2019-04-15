<?php

namespace Drupal\Tests\lightning_media\Kernel;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;

/**
 * @group lightning_media
 */
class EntityFormDisplayPresaveTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->container->get('module_installer')->install([
      'lightning_media_image',
      'lightning_page',
    ]);

    // Add a media reference field to Basic page.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_media',
      'entity_type' => 'node',
      'type' => 'entity_reference',
      'settings' => [
        'target_type' => 'media',
      ],
    ]);
    $field_storage->save();

    FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'page',
      'label' => 'Media',
      'settings' => [
        'handler_settings' => [
          'target_bundles' => [
            'image' => 'image',
          ],
        ],
      ],
    ])->save();
  }

  /**
   * Tests that components are not overridden if configured.
   */
  public function testNoOverride() {
    $this->config('lightning_media.settings')
      ->set('entity_browser.override_widget', FALSE)
      ->save();

    // Configure the component to use Select list.
    $display = EntityFormDisplay::load('node.page.default');
    $display->setComponent('field_media', [
      'type' => 'options_select',
    ]);
    $display->save();

    // Assert the configuration was not overridden.
    $display = EntityFormDisplay::load('node.page.default');
    $component = $display->getComponent('field_media');
    $this->assertInternalType('array', $component);
    $this->assertSame('options_select', $component['type']);
  }

  /**
   * Tests that components are overridden by default.
   */
  public function testOverride() {
    // Configure the component to use Select list.
    $display = EntityFormDisplay::load('node.page.default');
    $display->setComponent('field_media', [
      'type' => 'options_select',
    ]);
    $display->save();

    // Assert the configuration was overridden.
    $display = EntityFormDisplay::load('node.page.default');
    $component = $display->getComponent('field_media');
    $this->assertInternalType('array', $component);
    $this->assertSame('entity_browser_entity_reference', $component['type']);
  }

}
