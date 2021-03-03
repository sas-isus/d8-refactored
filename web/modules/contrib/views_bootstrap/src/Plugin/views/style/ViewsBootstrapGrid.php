<?php

namespace Drupal\views_bootstrap\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;
use Drupal\Component\Utility\Html;

/**
 * Style plugin to render each item in an ordered or unordered list.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "views_bootstrap_grid",
 *   title = @Translation("Bootstrap Grid"),
 *   help = @Translation("Displays rows in a Bootstrap Grid layout"),
 *   theme = "views_bootstrap_grid",
 *   theme_file = "../views_bootstrap.theme.inc",
 *   display_types = {"normal"}
 * )
 */
class ViewsBootstrapGrid extends StylePluginBase {
  /**
   * Overrides \Drupal\views\Plugin\views\style\StylePluginBase::usesRowPlugin.
   *
   * @var bool
   */
  protected $usesRowPlugin = TRUE;

  /**
   * Return the token-replaced row or column classes for the specified result.
   *
   * @param int $result_index
   *   The delta of the result item to get custom classes for.
   * @param string $type
   *   The type of custom grid class to return, either "row" or "col".
   *
   * @return string
   *   A space-delimited string of classes.
   */
  public function getCustomClass($result_index, $type) {
    if (isset($this->options[$type . '_class_custom'])) {
      $class = $this->options[$type . '_class_custom'];
      if ($this->usesFields() && $this->view->field) {
        $class = strip_tags($this->tokenizeValue($class, $result_index));
      }

      $classes = explode(' ', $class);
      foreach ($classes as &$class) {
        $class = Html::cleanCssIdentifier($class);
      }
      return implode(' ', $classes);
    }
    return '';
  }

  /**
   * Normalize a list of columns.
   *
   * Normalize columns based upon the fields that are available. This compares
   * the fields stored in the style handler
   * to the list of fields actually in the view, removing fields that
   * have been removed and adding new fields in their own column.
   * - Each field must be in a column.
   * - Each column must be based upon a field, and that field is somewhere in
   * the column.
   * - Any fields not currently represented must be added.
   * - Columns must be re-ordered to match the fields.
   *
   * @param array $columns
   *   An array of all fields; the key is the id of the field and the
   *   value is the id of the column the field should be in.
   * @param array|null $fields
   *   The fields to use for the columns. If not provided, they will
   *   be requested from the current display. The running render should
   *   send the fields through, as they may be different than what the
   *   display has listed due to access control or other changes.
   *
   * @return array
   *   An array of all the sanitized columns.
   */
  public function sanitizeColumns(array $columns, $fields = NULL) {
    $sanitized = [];
    if ($fields === NULL) {
      $fields = $this->displayHandler->getOption('fields');
    }
    // Pre-configure the sanitized array so that the order is retained.
    foreach ($fields as $field => $info) {
      // Set to itself so that if it isn't touched, it gets column
      // status automatically.
      $sanitized[$field] = $field;
    }

    if (!empty($columns)) {
      return $sanitized;
    }

    foreach ($columns as $field => $column) {
      // first, make sure the field still exists.
      if (!isset($sanitized[$field])) {
        continue;
      }

      // If the field is the column, mark it so, or the column
      // it's set to is a column, that's ok.
      if ($field == $column || $columns[$column] == $column && !empty($sanitized[$column])) {
        $sanitized[$field] = $column;
      }
      // Since we set the field to itself initially, ignoring
      // the condition is ok; the field will get its column
      // status back.
    }

    return $sanitized;
  }

  /**
   * Definition.
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['alignment'] = ['default' => 'horizontal'];
    $options['columns'] = ['default' => '12'];
    $options['col_xs'] = ['default' => 'col-xs-12'];
    $options['col_sm'] = ['default' => 'col-sm-6'];
    $options['col_md'] = ['default' => 'col-md-4'];
    $options['col_lg'] = ['default' => 'col-lg-3'];
    $options['automatic_width'] = ['default' => FALSE];
    $options['col_class_custom'] = ['default' => ''];
    $options['col_class_default'] = ['default' => TRUE];
    $options['row_class_custom'] = ['default' => ''];
    $options['row_class_default'] = ['default' => TRUE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['help'] = [
      '#markup' => $this->t('The Bootstrap grid displays content in a responsive, mobile first fluid grid (<a href=":docs">see documentation</a>).', [':docs' => 'https://www.drupal.org/docs/contributed-modules/views-bootstrap-for-bootstrap-3/grid']),
      '#weight' => -99,
    ];

    $form['alignment'] = [
      '#type' => 'radios',
      '#title' => $this->t('Alignment'),
      '#options' => [
        'horizontal' => $this->t('Horizontal'),
        'vertical' => $this->t('Vertical'),
      ],
      '#description' => $this->t('Horizontal alignment will place items starting in the upper left and moving right.
      Vertical alignment will place items starting in the upper left and moving down.'),
      '#default_value' => $this->options['alignment'],
    ];
    $form['col_class_default'] = [
      '#title' => $this->t('Default column classes'),
      '#description' => $this->t('Add the default views column classes like views-col, col-1 and clearfix to the output. You can use this to quickly reduce the amount of markup the view provides by default, at the cost of making it more difficult to apply CSS.'),
      '#type' => 'checkbox',
      '#default_value' => $this->options['col_class_default'],
    ];
    $form['col_class_custom'] = [
      '#title' => $this->t('Custom column class'),
      '#description' => $this->t('Additional classes to provide on each column. Separated by a space.'),
      '#type' => 'textfield',
      '#default_value' => $this->options['col_class_custom'],
    ];
    if ($this->usesFields()) {
      $form['col_class_custom']['#description'] .= ' ' . $this->t('You may use field tokens from as per the "Replacement patterns" used in "Rewrite the output of this field" for all fields.');
    }
    $form['row_class_default'] = [
      '#title' => $this->t('Default row classes'),
      '#description' => $this->t('Adds the default views row classes like views-row, row-1 and clearfix to the output. You can use this to quickly reduce the amount of markup the view provides by default, at the cost of making it more difficult to apply CSS.'),
      '#type' => 'checkbox',
      '#default_value' => $this->options['row_class_default'],
    ];
    $form['row_class_custom'] = [
      '#title' => $this->t('Custom row class'),
      '#description' => $this->t('Additional classes to provide on each row. Separated by a space.'),
      '#type' => 'textfield',
      '#default_value' => $this->options['row_class_custom'],
    ];
    if ($this->usesFields()) {
      $form['row_class_custom']['#description'] .= ' ' . $this->t('You may use field tokens from as per the "Replacement patterns" used in "Rewrite the output of this field" for all fields.');
    }
    $form['columns'] = [
      '#type' => 'select',
      '#title' => $this->t('Base number of columns'),
      '#default_value' => $this->options['columns'],
      '#required' => TRUE,
      '#options' => [
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        6 => 6,
        12 => 12,
        999 => $this->t('all'),
      ],
      '#description' => $this->t('Choose the number of columns that views will wrap in a single row. This will be reflected in the HTML structure rendered regardless of the device size. If selecting "all" then use the alternative template.'),
    ];

    foreach (['xs', 'sm', 'md', 'lg'] as $size) {
      $form["col_${size}"] = [
        '#type' => 'select',
        '#title' => $this->t("Number of columns (col-@size)", ['@size' => $size]),
        '#description' => $this->t("This adds col-@size to the div.", ['@size' => $size]),
        '#required' => TRUE,
        '#default_value' => isset($this->options["col_${size}"]) ? $this->options["col_${size}"] : NULL,
        '#options' => [
          "col-${size}-12" => 1,
          "col-${size}-6" => 2,
          "col-${size}-4" => 3,
          "col-${size}-3" => 4,
          "col-${size}-2" => 6,
          "col-${size}-1" => 12,
        ],
      ];
    }
  }

}
