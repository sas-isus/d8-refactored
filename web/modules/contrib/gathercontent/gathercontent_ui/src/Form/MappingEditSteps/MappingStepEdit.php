<?php

namespace Drupal\gathercontent_ui\Form\MappingEditSteps;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class MappingStepEdit.
 *
 * @package Drupal\gathercontent_ui\Form
 */
class MappingStepEdit extends MappingSteps {

  /**
   * {@inheritdoc}
   */
  public function getForm(FormStateInterface $formState) {
    $form = parent::getForm($formState);

    $filterFormats = filter_formats();
    $filterFormatOptions = [];

    foreach ($filterFormats as $key => $filterFormat) {
      $filterFormatOptions[$key] = $filterFormat->label();
    }

    $mappingData = unserialize($this->mapping->getData());
    $contentType = $this->mapping->getContentType();

    $form['gathercontent']['content_type'] = [
      '#type' => 'item',
      '#title' => t('Drupal content type:'),
      '#markup' => $this->mapping->getContentTypeName(),
      '#wrapper_attributes' => [
        'class' => [
          'inline-label',
        ],
      ],
    ];

    $form['mapping'] = [
      '#prefix' => '<div id="edit-mapping">',
      '#suffix' => '</div>',
    ];

    foreach ($this->template->config as $i => $fieldset) {
      if ($fieldset->hidden === FALSE) {
        $form['mapping'][$fieldset->id] = [
          '#type' => 'details',
          '#title' => $fieldset->label,
          '#open' => (array_search($i, array_keys($this->template->config)) === 0 ? TRUE : FALSE),
          '#tree' => TRUE,
        ];

        if (\Drupal::moduleHandler()->moduleExists('metatag')) {
          $form['mapping'][$fieldset->id]['type'] = [
            '#type' => 'select',
            '#options' => [
              'content' => t('Content'),
              'metatag' => t('Metatag'),
            ],
            '#title' => t('Type'),
            '#default_value' => (isset($mappingData[$fieldset->id]['type']) || $formState->hasValue($fieldset->id)['type']) ? ($formState->hasValue($fieldset->id)['type'] ? $formState->getValue($fieldset->id)['type'] : $mappingData[$fieldset->id]['type']) : 'content',
            '#ajax' => [
              'callback' => '::getMappingTable',
              'wrapper' => 'edit-mapping',
              'method' => 'replace',
              'effect' => 'fade',
            ],
          ];
        }

        if (\Drupal::moduleHandler()->moduleExists('content_translation') &&
          \Drupal::service('content_translation.manager')
            ->isEnabled('node', $formState->getValue('content_type'))
        ) {

          $form['mapping'][$fieldset->id]['language'] = [
            '#type' => 'select',
            '#options' => ['und' => t('None')] + $this->getLanguageList(),
            '#title' => t('Language'),
            '#default_value' => isset($mappingData[$fieldset->id]['language']) ? $mappingData[$fieldset->id]['language'] : 'und',
          ];
        }

        foreach ($fieldset->elements as $gc_field) {
          $d_fields = [];
          if (isset($formState->getTriggeringElement()['#name'])) {
            // We need different handling for changed fieldset.
            if ($formState->getTriggeringElement()['#array_parents'][1] === $fieldset->id) {
              if ($formState->getTriggeringElement()['#value'] === 'content') {
                $d_fields = $this->filterFields($gc_field, $contentType);
              }
              elseif ($formState->getTriggeringElement()['#value'] === 'metatag') {
                $d_fields = $this->filterMetatags($gc_field);
              }
            }
            else {
              if ($formState->getValue($fieldset->id)['type'] === 'content') {
                $d_fields = $this->filterFields($gc_field, $contentType);
              }
              elseif ($formState->getTriggeringElement()['#value'] === 'metatag') {
                $d_fields = $this->filterMetatags($gc_field);
              }
            }
          }
          else {
            if ((isset($mappingData[$fieldset->id]['type']) && $mappingData[$fieldset->id]['type'] === 'content') || !isset($mappingData[$fieldset->id]['type'])) {
              $d_fields = $this->filterFields($gc_field, $contentType);
            }
            else {
              $d_fields = $this->filterMetatags($gc_field);
            }
          }
          $form['mapping'][$fieldset->id]['elements'][$gc_field->id] = [
            '#type' => 'select',
            '#options' => $d_fields,
            '#title' => (!empty($gc_field->label) ? $gc_field->label : $gc_field->title),
            '#default_value' => isset($mappingData[$fieldset->id]['elements'][$gc_field->id]) ? $mappingData[$fieldset->id]['elements'][$gc_field->id] : NULL,
            '#empty_option' => t("Don't map"),
            '#attributes' => [
              'class' => [
                'gathercontent-ct-element',
              ],
            ],
          ];

          if (
            !$gc_field->plainText &&
            in_array($gc_field->type, ['text', 'section'])
          ) {
            $form['mapping'][$fieldset->id]['element_text_formats'][$gc_field->id] = [
              '#type' => 'select',
              '#options' => $filterFormatOptions,
              '#title' => (!empty($gc_field->label) ? $gc_field->label : $gc_field->title),
              '#default_value' => isset($mappingData[$fieldset->id]['element_text_formats'][$gc_field->id]) ? $mappingData[$fieldset->id]['element_text_formats'][$gc_field->id] : NULL,
              '#empty_option' => t("Choose text format"),
              '#attributes' => [
                'class' => [
                  'gathercontent-ct-element',
                ],
              ],
            ];
          }
        }

        $form['mapping'][$fieldset->id]['element_text_formats']['#type'] = 'details';
        $form['mapping'][$fieldset->id]['element_text_formats']['#title'] = t('Text format settings');
        $form['mapping'][$fieldset->id]['element_text_formats']['#open'] = FALSE;
      }
    }
    $form['mapping']['er_mapping_type'] = [
      '#type' => 'radios',
      '#title' => t('Taxonomy terms mapping type'),
      '#options' => [
        'automatic' => t('Automatic'),
        'semiautomatic' => t('Semi-automatic'),
        'manual' => t('Manual'),
      ],
      '#attributes' => [
        'class' => ['gathercontent-er-mapping-type'],
      ],
      '#description' => t("<strong>Automatic</strong> - taxonomy terms will be automatically created in predefined vocabulary. You cannot select translations. Field should be set as translatable for correct functionality.<br>
<strong>Semi-automatic</strong> - taxonomy terms will be imported into predefined vocabulary in the first language and we will offer you possibility to select their translations from other languages. For single language mapping this option will execute same action as 'Automatic' importField should not be set as translatable for correct functionality.<br>
<strong>Manual</strong> - you can map existing taxonomy terms from predefined vocabulary to translations in all languages."),
    ];

    return $form;
  }

}
