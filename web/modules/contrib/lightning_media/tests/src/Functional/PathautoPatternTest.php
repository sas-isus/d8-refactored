<?php

namespace Drupal\Tests\lightning_media\Functional;

use Drupal\media\Entity\Media;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;

/**
 * Tests that all media items have a /media/BUNDLE/ID Pathauto pattern.
 *
 * @group lightning_media
 */
class PathautoPatternTest extends BrowserTestBase {

  use MediaTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'pathauto',
    'lightning_media_document',
    'lightning_media_image',
    'lightning_media_instagram',
    'lightning_media_twitter',
    'lightning_media_video',
    'media_test_source',
  ];

  /**
   * Slick Entity Reference has a schema error.
   *
   * @todo Remove when depending on slick_entityreference 1.2 or later.
   *
   * @var bool
   */
  protected $strictConfigSchema = FALSE;

  /**
   * Tests media types that ship with Lightning.
   *
   * @param string $bundle
   *   Media bundle.
   * @param mixed $source_value
   *   (optional) The source field value.
   *
   * @dataProvider providerMediaPattern
   */
  public function testMediaPattern($bundle, $source_value = NULL) {
    /** @var \Drupal\media\MediaInterface $media */
    $media = Media::create([
      'bundle' => $bundle,
      'name' => 'Foo Bar',
    ]);

    if ($source_value) {
      $source_field = $media->getSource()
        ->getSourceFieldDefinition($media->bundle->entity)
        ->getName();

      $media->set($source_field, $source_value);
    }
    $this->assertSame(SAVED_NEW, $media->setPublished()->save());

    $this->drupalGet('/media/' . strtolower($media->bundle()) . '/' . $media->id());
    $assert = $this->assertSession();
    $assert->statusCodeEquals(200);
    $assert->pageTextContains('Foo Bar');
  }

  /**
   * Data provider for ::testMediaPattern().
   *
   * @return array
   *   The test data.
   */
  public function providerMediaPattern() {
    return [
      ['document'],
      ['image'],
      ['video'],
      ['tweet', 'https://twitter.com/50NerdsofGrey/status/757319527151636480'],
      ['instagram', 'https://www.instagram.com/p/BmIh_AFDBzX'],
    ];
  }

  /**
   * Tests a new media type.
   */
  public function testNewMediaTypePattern() {
    $media = Media::create([
      'bundle' => $this->createMediaType('test')->id(),
      'name' => 'Foo Bar',
    ]);

    $this->assertSame(SAVED_NEW, $media->setPublished()->save());
    $this->drupalGet("/media/{$media->bundle()}/{$media->id()}");

    $assert = $this->assertSession();
    $assert->statusCodeEquals(200);
    $assert->pageTextContains('Foo Bar');
  }

}
