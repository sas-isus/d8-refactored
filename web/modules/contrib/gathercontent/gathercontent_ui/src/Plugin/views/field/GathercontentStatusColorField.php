<?php

namespace Drupal\gathercontent_ui\Plugin\views\field;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("gathercontent_status_color_field")
 */
class GathercontentStatusColorField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->field_alias = 'item_status';
  }

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Do nothing -- to override the parent query.
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['hide_alter_empty'] = ['default' => FALSE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    /** @var \Drupal\gathercontent\Entity\OperationItem $entity */
    $entity = $values->_entity;
    return [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => ' ',
      '#attributes' => [
        'style' => 'width:20px; height: 20px; float: left; margin-right: 5px; background: ' . $entity->getItemStatusColor(),
      ],
    ];
  }

}
