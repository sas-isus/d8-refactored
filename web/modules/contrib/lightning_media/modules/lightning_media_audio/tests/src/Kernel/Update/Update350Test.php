<?php

namespace Drupal\Tests\lightning_media_audio\Kernel\Update;

use Drupal\field\Entity\FieldConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\lightning_media_audio\Update\Update350;
use Prophecy\Argument;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @group lightning_media
 * @group lightning_media_audio
 *
 * @coversDefaultClass \Drupal\lightning_media_audio\Update\Update350
 */
class Update350Test extends KernelTestBase {

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
      'lightning_media_audio',
    ]);

    FieldConfig::loadByName('media', 'audio_file', 'field_media_in_library')
      ->setTranslatable(TRUE)
      ->save();
  }

  /**
   * @covers ::removeAudioFileLibraryFieldTranslatability
   */
  public function test() {
    $io = $this->prophesize(StyleInterface::class);
    $io->confirm(Argument::type('string'))->willReturn(TRUE);

    Update350::create($this->container)
      ->removeAudioFileLibraryFieldTranslatability($io->reveal());

    $this->assertFalse(
      FieldConfig::loadByName('media', 'audio_file', 'field_media_in_library')->isTranslatable()
    );
  }

}
