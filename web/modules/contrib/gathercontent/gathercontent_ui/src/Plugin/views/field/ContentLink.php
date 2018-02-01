<?php

namespace Drupal\gathercontent_ui\Plugin\views\field;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\Plugin\views\field\Path;
use Drupal\views\ResultRow;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("content_link")
 */
class ContentLink extends Path {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $nid = $this->getValue($values, 'nid');
    if (is_numeric($nid)) {
      $url = Url::fromRoute('entity.node.canonical', ['node' => $nid], ['absolute' => $this->options['absolute']]);
      return Link::fromTextAndUrl($this->t('Open'), $url)->toRenderable();
    }
    else {
      return $this->t('Not available');
    }
  }

}
