<?php

namespace Drupal\lightning_media_video\Plugin\media\Source;

use Drupal\lightning_media\FileInputExtensionMatchTrait;
use Drupal\lightning_media\InputMatchInterface;
use Drupal\media\Plugin\media\Source\VideoFile as CoreVideoFile;

/**
 * Input-matching version of the VideoFile media source.
 */
class VideoFile extends CoreVideoFile implements InputMatchInterface {

  use FileInputExtensionMatchTrait;

}
