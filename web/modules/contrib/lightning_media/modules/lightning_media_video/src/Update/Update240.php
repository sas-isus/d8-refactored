<?php

namespace Drupal\lightning_media_video\Update;

use Drupal\lightning_core\ConfigHelper;

/**
 * @Update("2.4.0")
 */
final class Update240 {

  /**
   * Installs a media type for locally hosted video files.
   *
   * @update
   *
   * @ask Do you want to install the "Video file" media type?
   */
  public function installVideoFileMedia() {
    $helper = ConfigHelper::forModule('lightning_media_video');

    $helper->getEntity('media_type', 'video_file')->save();
    $helper->getEntity('field_storage_config', 'media.field_media_video_file')->save();
    $helper->getEntity('field_config', 'media.video_file.field_media_video_file')->save();
    $helper->getEntity('field_config', 'media.video_file.field_media_in_library')->save();
    $helper->getEntity('entity_form_display', 'media.video_file.media_browser')->save();
    $helper->getEntity('entity_view_display', 'media.video_file.embedded')->save();
    $helper->getEntity('entity_view_display', 'media.video_file.thumbnail')->save();

    // Handle entity displays as simple config, since they are created
    // automatically when the media type is imported.
    $helper->get('core.entity_form_display.media.video_file.default')->save(TRUE);
    $helper->get('core.entity_view_display.media.video_file.default')->save(TRUE);
  }

}
