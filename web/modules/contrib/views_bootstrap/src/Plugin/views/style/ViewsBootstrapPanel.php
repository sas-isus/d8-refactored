<?php

namespace Drupal\views_bootstrap\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Style plugin to render each item as a row in a Bootstrap Panel.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "views_bootstrap_panel",
 *   title = @Translation("Bootstrap Panels"),
 *   help = @Translation("Displays rows in a Bootstrap Panel."),
 *   theme = "views_bootstrap_panel",
 *   theme_file = "../views_bootstrap.theme.inc",
 *   display_types = {"normal"}
 * )
 */
class ViewsBootstrapPanel extends StylePluginBase {
  /**
   * Does the style plugin for itself support to add fields to it's output.
   *
   * @var bool
   */
  protected $usesFields = TRUE;

  /**
   * Does the style plugin allows to use style plugins.
   *
   * @var bool
   */
  protected $usesRowPlugin = TRUE;

  /**
   * Definition.
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['panel_title_field'] = ['default' => NULL];
    $options['panel_label_field'] = ['default' => NULL];
    $options['panel_footer_field'] = ['default' => NULL];
    $options['contextual_class'] = ['default' => 'panel-default'];

    return $options;
  }

  /**
   * Render the given style.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['help'] = [
      '#markup' => $this->t('The Bootstrap panels displays content in a box with optional header and footer elements (<a href=":docs">see documentation</a>).', [':docs' => 'https://www.drupal.org/docs/contributed-modules/views-bootstrap-for-bootstrap-3/panels']),
      '#weight' => -99,
    ];

    $form['contextual_class'] = [
      '#type' => 'radios',
      '#title' => $this->t('Contextual class'),
      '#options' => [
        'panel-default' => $this->t('Default'),
        'panel-primary' => $this->t('Primary'),
        'panel-success' => $this->t('Success'),
        'panel-info' => $this->t('Info'),
        'panel-warning' => $this->t('Warning'),
        'panel-danger' => $this->t('Danger'),
      ],
      '#default_value' => $this->options['contextual_class'],
      '#description' => $this->t('<a href=":docs">see Bootstrap documentation</a>', [':docs' => 'https://getbootstrap.com/docs/3.4/components/#panels-alternatives']),
    ];

    $form['panel_title_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Panel title field'),
      '#options' => ['' => $this->t('- None -')] + $this->displayHandler->getFieldLabels(TRUE),
      '#default_value' => $this->options['panel_title_field'],
      '#description' => $this->t('Select the field that will be used as the panel heading titles.'),
    ];

    $form['panel_label_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Label field'),
      '#options' => ['' => $this->t('- None -')] + $this->displayHandler->getFieldLabels(TRUE),
      '#required' => FALSE,
      '#default_value' => $this->options['label_field'],
      '#description' => $this->t('Select the field that will be used as the label.'),
    ];

    $form['panel_footer_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Panel footer field'),
      '#options' => ['' => $this->t('- None -')] + $this->displayHandler->getFieldLabels(TRUE),
      '#default_value' => $this->options['panel_title_field'],
      '#description' => $this->t('Select the field that will be used as the panel footer.'),
    ];
  }

}
