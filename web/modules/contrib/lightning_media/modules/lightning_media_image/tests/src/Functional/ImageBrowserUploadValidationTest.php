<?php

namespace Drupal\Tests\lightning_media_image\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\entity_browser\Element\EntityBrowserElement;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;

/**
 * @group lightning_media
 * @group lightning_media_image
 */
class ImageBrowserUploadValidationTest extends BrowserTestBase {

  use ContentTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'lightning_media_image',
    'node',
  ];

  /**
   * Data provider for testValidation().
   *
   * @return array
   */
  public function providerValidation() {
    return [
      'file extension' => [
        'test.php',
        'Only files with the following extensions are allowed',
      ],
      'file size' => [
        'test.jpg',
        'exceeding the maximum file size of 5 KB',
      ],
    ];
  }

  /**
   * Tests that the upload widget validates input correctly.
   *
   * @param string $file
   *   The name of the file to upload (in ../../files).
   * @param string $expected_error
   *   The expected error message.
   *
   * @dataProvider providerValidation
   */
  public function testValidation($file, $expected_error) {
    $node_type = $this->createContentType();

    /** @var \Drupal\field\FieldStorageConfigInterface $field_storage */
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_lightweight_image',
      'entity_type' => 'node',
      'type' => 'image',
      'cardinality' => 1,
    ]);
    $this->assertSame(SAVED_NEW, $field_storage->save());

    FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => $node_type->id(),
      'label' => 'Lightweight Image',
      'settings' => [
        'max_filesize' => '5 KB',
      ]
    ])->save();

    entity_get_form_display('node', $node_type->id(), 'default')
      ->setComponent('field_lightweight_image', [
        'type' => 'entity_browser_file',
        'settings' => [
          'entity_browser' => 'image_browser',
          'field_widget_edit' => TRUE,
          'field_widget_remove' => TRUE,
          'view_mode' => 'default',
          'preview_image_style' => 'thumbnail',
          'open' => TRUE,
          'selection_mode' => EntityBrowserElement::SELECTION_MODE_APPEND,
        ],
        'region' => 'content',
      ])
      ->save();

    $account = $this->createUser([
      'create media',
      'create ' . $node_type->id() . ' content',
      'access image_browser entity browser pages',
    ]);
    $this->drupalLogin($account);

    $this->drupalGet('/node/add/' . $node_type->id());
    $this->assertSession()->statusCodeEquals(200);

    $settings = $this->assertSession()
      ->elementExists('css', '[data-drupal-selector="drupal-settings-json"]')
      ->getText();

    $settings = Json::decode($settings);
    $this->assertArrayHasKey('entity_browser', $settings);

    $settings = reset($settings['entity_browser']['modal']);

    $url = $this->buildUrl('/entity-browser/modal/image_browser', [
      'query' => [
        'uuid' => $settings['uuid'],
        'original_path' => $settings['original_path'],
      ],
    ]);
    $this->drupalGet($url);
    $this->assertSession()->statusCodeEquals(200);

    $file_field = $this->assertSession()->elementExists('css', '.js-form-managed-file');
    $file_field->attachFileToField('File', __DIR__ . "/../../files/$file");
    $file_field->pressButton('Upload');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementExists('css', '[role="alert"]');
    $this->assertSession()->pageTextContains($expected_error);
    // The error message should not be double-escaped.
    $this->assertSession()->responseNotContains('&lt;em class="placeholder"&gt;');
    $this->assertSession()->elementExists('css', 'input.form-file.error');
  }

}
