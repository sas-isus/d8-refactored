<?php

namespace Drupal\views_bootstrap\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Style plugin to render each item in an ordered or unordered list.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "views_bootstrap_list_group",
 *   title = @Translation("Bootstrap List Group"),
 *   help = @Translation("Displays rows in a Bootstrap List Group."),
 *   theme = "views_bootstrap_list_group",
 *   theme_file = "../views_bootstrap.theme.inc",
 *   display_types = {"normal"}
 * )
 */
class ViewsBootstrapListGroup extends StylePluginBase {

  /**
   * Overrides \Drupal\views\Plugin\views\style\StylePluginBase::usesRowPlugin.
   *
   * @var bool
   */
  protected $usesRowPlugin = TRUE;

  /**
   * Overrides \Drupal\views\Plugin\views\style\StylePluginBase::usesRowClass.
   *
   * @var bool
   */
  protected $usesRowClass = TRUE;

  /**
   * Definition.
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['link_field'] = ['default' => []];
    $options['panels'] = ['default' => FALSE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['help'] = [
      '#markup' => $this->t('The Bootstrap list group displays content in an unordered list with list group classes (<a href=":docs">see documentation</a>).', [':docs' => 'https://www.drupal.org/docs/contributed-modules/views-bootstrap-for-bootstrap-3/list-group']),
      '#weight' => -99,
    ];

    $fields = ['' => $this->t('<None>')];
    $fields += $this->displayHandler->getFieldLabels(TRUE);

    $form['panels'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Set group field in panel heading'),
      '#default_value' => $this->options['panels'],
      '#states' => [
        'invisible' => [
          ':input[name="style_options[grouping][0][field]"]' => ['value' => ''],
        ],
      ],
      '#weight' => 0,
    ];

    $form['title_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Title field'),
      '#options' => $fields,
      '#required' => FALSE,
      '#default_value' => $this->options['title_field'],
      '#description' => $this->t('Select the field that will be used as the title.'),
    ];

  }

}
