<?php

namespace Drupal\Tests\lightning_media\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * @group lightning_media
 */
class MediaImageFieldTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'lightning_media_image',
    'lightning_media_video',
  ];

  /**
   * Tests clearing an image field on an existing media item.
   */
  public function test() {
    $field_name = 'field_test' . mb_strtolower($this->randomMachineName());

    $field_storage = entity_create('field_storage_config', [
      'field_name' => $field_name,
      'entity_type' => 'media',
      'type' => 'image',
    ]);
    $this->assertSame(SAVED_NEW, $field_storage->save());

    entity_create('field_config', [
      'field_storage' => $field_storage,
      'bundle' => 'video',
      'label' => 'Image',
    ])->save();

    $this->drupalPlaceBlock('local_tasks_block');

    $form_display = entity_get_form_display('media', 'video', 'default');
    // Add field_image to the display and save it; lightning_media_image will
    // default it to the image browser widget.
    $form_display->setComponent($field_name, ['type' => 'image_image'])->save();
    // Then switch it to a standard image widget.
    $form_display
      ->setComponent($field_name, [
        'type' => 'image_image',
        'weight' => 4,
        'settings' => [
          'preview_image_style' => 'thumbnail',
          'progress_indicator' => 'throbber',
        ],
        'region' => 'content',
      ])
      ->save();

    $account = $this->createUser([
      'create media',
      'update media',
    ]);
    $this->drupalLogin($account);

    $name = $this->randomString();

    $this->drupalGet('/media/add/video');
    $this->assertSession()->fieldExists('Name')->setValue($name);
    $this->assertSession()->fieldExists('Video URL')->setValue('https://www.youtube.com/watch?v=z9qY4VUZzcY');
    $this->assertSession()->waitForField('Image')->attachFile(__DIR__ . '/../../files/test.jpg');
    $this->assertSession()->waitForField('Alternative text')->setValue('This is a beauty.');
    $this->assertSession()->buttonExists('Save')->press();
    $this->assertSession()->elementExists('named', ['link', 'Edit'])->click();
    $this->assertSession()->buttonExists("{$field_name}_0_remove_button")->press();
    $this->assertSession()->assertWaitOnAjaxRequest();
    // Ensure that the widget has actually been cleared. This test was written
    // because the AJAX operation would fail due to a 500 error at the server,
    // which would prevent the widget from being cleared.
    $this->assertSession()->buttonNotExists("{$field_name}_0_remove_button");
  }

}
