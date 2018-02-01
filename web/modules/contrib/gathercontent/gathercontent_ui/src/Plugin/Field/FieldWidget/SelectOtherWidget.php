<?php

namespace Drupal\gathercontent_ui\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'select_other_widget' widget.
 *
 * @FieldWidget(
 *   id = "select_other_widget",
 *   label = @Translation("Select or other"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class SelectOtherWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'available_options' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['available_options'] = [
      '#type' => 'textarea',
      '#title' => t('Available options'),
      '#description' => t('A list of values that are, by default, available for selection. Enter one value per line, in the format key|label. The key is the value that will be stored in the database, and the label is what will be displayed to the user.'),
      '#default_value' => $this->getSetting('available_options'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $options = $this->prepareOptions();
    $default_value = NULL;
    if (isset($items[$delta]->value)) {
      if (isset($options[$items[$delta]->value])) {
        $default_value = $items[$delta]->value;
      }
      else {
        $default_value = 'other';
      }
    }
    $element['select'] = $element + [
      '#type' => 'radios',
      '#options' => $options,
      '#default_value' => $default_value,
    ];

    $element['other'] = [
      '#type' => 'textfield',
      '#size' => 60,
      '#default_value' => (isset($items[$delta]->value) && !isset($options[$items[$delta]->value])) ? $items[$delta]->value : '',
    ];

    return $element;
  }

  /**
   * Get list of options.
   *
   * @return array
   *   Array of options with appended Other option.
   */
  protected function prepareOptions() {
    $list = explode("\n", $this->getSetting('available_options'));
    $options = [];
    foreach ($list as $item) {
      list($key, $value) = explode('|', $item);
      $value = html_entity_decode($value);
      $options[$key] = $value;
    }
    return $options + ['other' => $this->t('Other')];
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $new_values = [];
    foreach ($values as $value) {
      if ($value['select'] === 'other') {
        $new_values[] = ['value' => $value['other']];
      }
      else {
        $new_values[] = ['value' => $value['select']];

      }
    }

    return $new_values;
  }

}
