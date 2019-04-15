<?php

namespace Drupal\Tests\lightning_media_audio\Kernel;

use Drupal\file\Entity\File;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\media\Entity\Media;

/**
 * @group lightning_media
 * @group lightning_media_audio
 */
class LibraryInclusionTranslationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->container->get('module_installer')->install([
      'content_translation',
      'lightning_media_audio',
    ]);
    ConfigurableLanguage::createFromLangcode('hu')->save();
  }

  public function test() {
    $uri = uniqid('public://') . '.mp3';
    $this->assertGreaterThan(0, file_put_contents($uri, $this->getRandomGenerator()->paragraphs()));

    $file = File::create(['uri' => $uri]);
    $this->assertSame(SAVED_NEW, $file->save());

    $media = Media::create([
      'bundle' => 'audio_file',
      'name' => $this->randomString(),
      'field_media_audio_file' => $file->id(),
      'field_media_in_library' => TRUE,
    ]);
    $media->addTranslation('hu', [
      'field_media_in_library' => FALSE,
    ]);
    $this->assertSame(SAVED_NEW, $media->save());

    $this->assertTrue($media->field_media_in_library->value);
    $this->assertTrue($media->getTranslation('hu')->field_media_in_library->value);
  }

}
