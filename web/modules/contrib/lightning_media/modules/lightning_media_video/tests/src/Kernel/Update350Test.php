<?php

namespace Drupal\Tests\lightning_media_video\Kernel\Update;

use Drupal\field\Entity\FieldConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\lightning_media_video\Update\Update350;
use Prophecy\Argument;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @group lightning_media
 * @group lightning_media_video
 *
 * @coversDefaultClass \Drupal\lightning_media_video\Update\Update350
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
      'lightning_media_video',
    ]);

    FieldConfig::loadByName('media', 'video', 'field_media_in_library')
      ->setTranslatable(TRUE)
      ->save();

    FieldConfig::loadByName('media', 'video_file', 'field_media_in_library')
      ->setTranslatable(TRUE)
      ->save();
  }

  /**
   * @covers ::removeVideoFileLibraryFieldTranslatability
   * @covers ::removeVideoLibraryFieldTranslatability
   */
  public function test() {
    $io = $this->prophesize(StyleInterface::class);
    $io->confirm(Argument::type('string'))->willReturn(TRUE);

    $task = Update350::create($this->container);
    $task->removeVideoFileLibraryFieldTranslatability($io->reveal());
    $task->removeVideoLibraryFieldTranslatability($io->reveal());

    $this->assertFalse(
      FieldConfig::loadByName('media', 'video_file', 'field_media_in_library')->isTranslatable()
    );
    $this->assertFalse(
      FieldConfig::loadByName('media', 'video', 'field_media_in_library')->isTranslatable()
    );
  }

}
