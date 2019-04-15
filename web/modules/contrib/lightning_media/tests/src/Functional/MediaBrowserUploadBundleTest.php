<?php

namespace Drupal\Tests\lightning_media\Functional;

use Drupal\entity_browser\Element\EntityBrowserElement;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;

/**
 * @group lightning_media
 */
class MediaBrowserUploadBundleTest extends BrowserTestBase {

  use ContentTypeCreationTrait;
  use MediaTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'lightning_media_image',
    'node',
  ];

  /**
   * The content type created during the test.
   *
   * @var \Drupal\node\NodeTypeInterface
   */
  private $nodeType;

  /**
   * The media type created during the test.
   *
   * @var \Drupal\media\MediaTypeInterface
   */
  private $mediaType;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->nodeType = $this->createContentType();
    $this->mediaType = $this->createMediaType('image');

    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_z_image',
      'entity_type' => 'node',
      'type' => 'entity_reference',
      'settings' => [
        'target_type' => 'media',
      ],
    ]);
    $this->assertSame(SAVED_NEW, $field_storage->save());

    FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => $this->nodeType->id(),
      'label' => 'Z Image',
      'settings' => [
        'handler_settings' => [
          'target_bundles' => [
            $this->mediaType->id() => $this->mediaType->id(),
          ],
        ],
      ],
    ])->save();

    entity_get_form_display('node', $this->nodeType->id(), 'default')
      ->setComponent('field_z_image', [
        'type' => 'entity_browser_entity_reference',
        'settings' => [
          'entity_browser' => 'media_browser',
          'field_widget_display' => 'rendered_entity',
          'field_widget_edit' => TRUE,
          'field_widget_remove' => TRUE,
          'selection_mode' => EntityBrowserElement::SELECTION_MODE_APPEND,
          'field_widget_display_settings' => [
            'view_mode' => 'embedded',
          ],
          'open' => TRUE,
        ],
        'region' => 'content',
      ])
      ->save();
  }

  /**
   * Tests that the upload widget validates file extensions.
   */
  public function testFileExtensionValidation() {
    $account = $this->createUser([
      'access media_browser entity browser pages',
      'create media',
    ]);
    $this->drupalLogin($account);

    $this->drupalGet('/entity-browser/modal/media_browser');
    $this->assertSession()->statusCodeEquals(200);

    $file_field = $this->assertSession()->elementExists('css', '.js-form-managed-file');
    $file_field->attachFileToField('File', __DIR__ . "/../../files/test.php");
    $file_field->pressButton('Upload');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementExists('css', '[role="alert"]');
    $this->assertSession()->pageTextContains('Only files with the following extensions are allowed');
  }

  /**
   * Tests that the upload widget respects media types allowed by the field.
   */
  public function testFilterFieldSettings() {
    $account = $this->createUser([
      'create ' . $this->nodeType->id() . ' content',
      'access media_browser entity browser pages',
      'create media',
    ]);
    $this->drupalLogin($account);

    $this->drupalGet('/node/add/' . $this->nodeType->id());
    $this->assertSession()->statusCodeEquals(200);

    $uuid = $this->assertSession()
      ->buttonExists('Add media')
      ->getAttribute('data-uuid');
    $this->assertNotEmpty($uuid);

    $this->drupalGet("/entity-browser/modal/media_browser", [
      'query' => [
        'uuid' => $uuid,
      ],
    ]);
    $this->assertSession()->statusCodeEquals(200);

    $file_field = $this->assertSession()
      ->elementExists('css', '.js-form-managed-file');
    $file_field->attachFileToField('File', __DIR__ . '/../../files/test.jpg');
    $file_field->pressButton('Upload');

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldNotExists('Bundle');
    $this->assertSession()->fieldExists('Name')->setValue($this->randomString());
    $this->assertSession()->fieldExists('Alternative text')->setValue($this->randomString());
    $this->assertSession()->buttonExists('Place')->press();
    $this->assertSession()->statusCodeEquals(200);

    /** @var \Drupal\Core\Entity\EntityStorageInterface $storage */
    $storage = $this->container->get('entity_type.manager')
      ->getStorage('media');

    $media = $storage->loadByProperties([
      'bundle' => $this->mediaType->id(),
    ]);
    $this->assertCount(1, $media);
    $storage->delete($media);

    $this->assertCount(0, $storage->getQuery()->condition('bundle', 'image')->execute());
  }

}
