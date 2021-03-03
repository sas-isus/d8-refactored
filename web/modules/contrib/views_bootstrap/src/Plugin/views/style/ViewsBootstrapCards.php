<?php

namespace Drupal\views_bootstrap\Plugin\views\style;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Style plugin to render each item in an ordered or unordered list.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "views_bootstrap_cards",
 *   title = @Translation("Bootstrap Cards"),
 *   help = @Translation("Displays rows in a Bootstrap Card Group layout"),
 *   theme = "views_bootstrap_cards",
 *   theme_file = "../views_bootstrap.theme.inc",
 *   display_types = {"normal"}
 * )
 */
class ViewsBootstrapCards extends StylePluginBase {
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
   * Return the token-replaced row or column classes for the specified result.
   *
   * @param int $result_index
   *   The delta of the result item to get custom classes for.
   * @param string $type
   *   The type of custom grid class to return, either "card_group" or "card".
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
   * Definition.
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['card_title_field'] = ['default' => NULL];
    $options['card_content_field'] = ['default' => NULL];
    $options['card_image_field'] = ['default' => NULL];
    $options['card_group_class_custom'] = ['default' => NULL];
    $options['card_class_custom'] = ['default' => NULL];
    return $options;
  }

  /**
   * Render the given style.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    if (isset($form['grouping'])) {
      unset($form['grouping']);
      $form['card_title_field'] = [
        '#type' => 'select',
        '#title' => $this->t('Card title field'),
        '#options' => $this->displayHandler->getFieldLabels(TRUE),
        '#required' => TRUE,
        '#default_value' => $this->options['card_title_field'],
        '#description' => $this->t('Select the field that will be used for the card title.'),
      ];
      $form['card_content_field'] = [
        '#type' => 'select',
        '#title' => $this->t('Card content field'),
        '#options' => $this->displayHandler->getFieldLabels(TRUE),
        '#required' => TRUE,
        '#default_value' => $this->options['card_content_field'],
        '#description' => $this->t('Select the field that will be used for the card content.'),
      ];
      $form['card_image_field'] = [
        '#type' => 'select',
        '#title' => $this->t('Card image field'),
        '#options' => $this->displayHandler->getFieldLabels(TRUE),
        '#required' => TRUE,
        '#default_value' => $this->options['card_image_field'],
        '#description' => $this->t('Select the field that will be used for the card image.'),
      ];
      $form['card_group_class_custom'] = [
        '#title' => $this->t('Custom card group class'),
        '#description' => $this->t('Additional classes to provide on the card group. Separated by a space.'),
        '#type' => 'textfield',
        '#default_value' => $this->options['card_group_class_custom'],
      ];
      $form['card_class_custom'] = [
        '#title' => $this->t('Custom card group class'),
        '#description' => $this->t('Additional classes to provide on each card. Separated by a space.'),
        '#type' => 'textfield',
        '#default_value' => $this->options['card_class_custom'],
      ];
    }
  }

}
