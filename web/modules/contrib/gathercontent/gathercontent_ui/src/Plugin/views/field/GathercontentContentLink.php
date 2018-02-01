<?php

namespace Drupal\gathercontent_ui\Plugin\views\field;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\Plugin\views\field\Path;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("gathercontent_content_link")
 */
class GathercontentContentLink extends Path {

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    $this->additional_fields['gc_id'] = 'gc_id';
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $gc_id = $this->getValue($values, 'gc_id');
    if (is_numeric($gc_id)) {
      $base_url = 'https://' . \Drupal::config('gathercontent.settings')
        ->get('gathercontent_urlkey') . '.gathercontent.com/item/';
      $url = Url::fromUri($base_url . $gc_id);
      return Link::fromTextAndUrl($this->t('Open'), $url)->toRenderable();
    }
    else {
      return $this->t('Not available');
    }
  }

}
