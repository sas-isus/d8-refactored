<?php

namespace Drupal\Tests\lightning_media_image\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * @group lightning_media
 * @group lightning_media_image
 */
class InstallTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'lightning_media_image',
    'image_widget_crop',
  ];

  /**
   * Slick Entity Reference has a schema error.
   *
   * @todo Remove when depending on slick_entityreference 1.2 or later.
   *
   * @var bool
   */
  protected $strictConfigSchema = FALSE;

  public function test() {
    // Assert that a local copy of the Cropper library is being used.
    $settings = $this->config('image_widget_crop.settings')->get('settings');
    $lib = 'libraries/cropper/dist';
    $this->assertContains("$lib/cropper.min.js", $settings['library_url']);
    $this->assertContains("$lib/cropper.min.css", $settings['css_url']);

    $form_displays = $this->container
      ->get('entity_type.manager')
      ->getStorage('entity_form_display')
      ->loadByProperties([
        'targetEntityType' => 'media',
        'bundle' => 'image',
      ]);

    /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $form_display */
    foreach ($form_displays as $form_display) {
      $component = $form_display->getComponent('image');
      $this->assertInternalType('array', $component);
      $this->assertSame('image_widget_crop', $component['type']);
      $this->assertSame(['freeform'], $component['settings']['crop_list']);
    }
  }

}
