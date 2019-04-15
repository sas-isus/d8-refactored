<?php

namespace Drupal\Tests\lightning_media\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests available libraries required by Lightning Media.
 *
 * @group lightning_media
 */
class MediaJsLibrariesTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'libraries',
  ];

  /**
   * Test that Slick and Blazy JS libraries are available through Libraries API.
   */
  public function testJavascriptLibrariesAvailability() {
    // Testing Slick JS library:
    // @see slick_library_info_alter().
    $this->assertNotEmpty(libraries_get_path('slick') ?: libraries_get_path('slick-carousel'), 'libraries_get_path() returns path for Slick library.');

    // Blazy library:
    // @see blazy_library_info_alter().
    $this->assertNotEmpty(libraries_get_path('blazy'), 'libraries_get_path() returns path for Blazy library.');
  }

}
