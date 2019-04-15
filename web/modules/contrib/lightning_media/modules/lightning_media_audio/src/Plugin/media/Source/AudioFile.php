<?php

namespace Drupal\lightning_media_audio\Plugin\media\Source;

use Drupal\lightning_media\FileInputExtensionMatchTrait;
use Drupal\lightning_media\InputMatchInterface;
use Drupal\media\Plugin\media\Source\AudioFile as CoreAudioFile;

/**
 * Input-matching version of the AudioFile media source.
 */
class AudioFile extends CoreAudioFile implements InputMatchInterface {

  use FileInputExtensionMatchTrait;

}
