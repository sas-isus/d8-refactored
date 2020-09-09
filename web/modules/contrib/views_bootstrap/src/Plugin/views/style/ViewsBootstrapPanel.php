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
 *   title = @Translation("Bootstrap Panel"),
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

    return $options;
  }

  /**
   * Render the given style.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $form['panel_title_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Panel title field'),
      '#options' => $this->displayHandler->getFieldLabels(TRUE),
      '#required' => TRUE,
      '#default_value' => $this->options['panel_title_field'],
      '#description' => $this->t('Select the field that will be used as the panel heading titles.'),
    ];
  }

}
