<?php

namespace Drupal\Tests\lightning_media\Kernel\Update;

use Drupal\KernelTests\KernelTestBase;

/**
 * @group lightning_media
 */
class Update8020Test extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'lightning_media'];

  public function testUpdate() {
    $setting = $this->config('lightning_media.settings')
      ->get('entity_browser.override_widget');
    $this->assertNull($setting);

    module_load_install('lightning_media');
    lightning_media_update_8020();

    $setting = $this->config('lightning_media.settings')
      ->get('entity_browser.override_widget');
    $this->assertTrue($setting);
  }

}
