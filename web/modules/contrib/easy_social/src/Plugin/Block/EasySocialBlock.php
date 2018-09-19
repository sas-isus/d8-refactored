<?php

/**
 * @file
 * Contains \Drupal\easy_social\Plugin\Block\EasySocialBlock.
 */

namespace Drupal\easy_social\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Provides an Easy Social block.
 *
 * @Block(
 *   id = "easy_social_block",
 *   admin_label = @Translation("Easy Social"),
 * )
 */
class EasySocialBlock extends BlockBase {

  /**
   * Implements \Drupal\Block\BlockBase::blockBuild().
   */
  public function build() {
    $content = array(
      '#theme' => 'easy_social',
    );

    return array(
      '#children' => \Drupal::service('renderer')->render($content),
    );
  }

}
