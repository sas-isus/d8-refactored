<?php

namespace Drupal\Tests\lightning_media\Functional;

use Drupal\field\Entity\FieldConfig;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;

/**
 * @group lightning_media
 * @group orca_public
 */
class MediaTypeDisambiguationTest extends BrowserTestBase {

  use MediaTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'lightning_media_document',
    'lightning_media_image',
    'lightning_media_video',
  ];

  /**
   * The media entity storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $storage;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->storage = $this->container->get('entity_type.manager')
      ->getStorage('media');

    FieldConfig::loadByName('media', 'document', 'field_document')
      ->setSetting('file_extensions', 'txt pdf doc docx jpg')
      ->save();

    $this->createMediaType('video_embed_field', [
      'id' => 'test_video_2',
      'label' => 'Test Video 2',
    ]);

    $account = $this->createUser([
      'create media',
      'access media_browser entity browser pages',
      'access media overview',
    ]);
    $this->drupalLogin($account);

    $this->drupalGet('/entity-browser/modal/media_browser');
    $this->assertSession()->statusCodeEquals(200);
  }

  public function testUpload() {
    $file_field = $this->assertSession()->elementExists('css', '.js-form-managed-file');
    $file_field->attachFileToField('File', __DIR__ . '/../../files/test.jpg');
    $file_field->pressButton('Upload');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldNotExists('Name');
    $this->assertSession()->selectExists('Bundle')->selectOption('Image');
    $this->assertSession()->buttonExists('Update')->press();
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldNotExists('Bundle');
    $this->assertSession()->fieldExists('Name')->setValue('Foobaz');
    $this->assertSession()->buttonExists('Place')->press();
    $this->assertSession()->statusCodeEquals(200);

    $media = $this->storage->getQuery()
      ->condition('name', 'Foobaz')
      ->condition('bundle', 'image')
      ->execute();
    $this->assertCount(1, $media);
  }

  public function testEmbedCode() {
    $this->assertSession()->buttonExists('Create embed')->press();
    $this->assertSession()->fieldExists('input')->setValue('https://www.youtube.com/watch?v=zQ1_IbFFbzA');
    $this->assertSession()->buttonExists('Update')->press();
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldNotExists('Name');
    $this->assertSession()->selectExists('Bundle')->selectOption('Test Video 2');
    $this->assertSession()->buttonExists('Update')->press();
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldNotExists('Bundle');
    $this->assertSession()->fieldExists('Name')->setValue('Foobaz');
    $this->assertSession()->buttonExists('Place')->press();
    $this->assertSession()->statusCodeEquals(200);
    $this->drupalGet('/admin/content/media-table');
    $this->assertSession()->elementExists('named', ['link', 'Foobaz']);
    $this->assertSession()->pageTextContains('Test Video 2');

    $media = $this->storage->getQuery()
      ->condition('name', 'Foobaz')
      ->condition('bundle', 'test_video_2')
      ->execute();
    $this->assertCount(1, $media);
  }

}
