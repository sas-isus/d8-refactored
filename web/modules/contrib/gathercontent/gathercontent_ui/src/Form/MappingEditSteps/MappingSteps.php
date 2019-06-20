<?php

namespace Drupal\gathercontent_ui\Form\MappingEditSteps;

use Cheppers\GatherContent\DataTypes\Element;
use Cheppers\GatherContent\DataTypes\ElementText;
use Cheppers\GatherContent\DataTypes\Template;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\gathercontent\Entity\MappingInterface;

/**
 * Class MappingSteps.
 *
 * @package Drupal\gathercontent_ui\Form
 */
abstract class MappingSteps {

  use StringTranslationTrait;

  /**
   * Mapping object.
   *
   * @var \Drupal\gathercontent\Entity\Mapping
   */
  public $mapping;

  /**
   * Template object.
   *
   * @var \Cheppers\GatherContent\DataTypes\Template
   */
  public $template;

  /**
   * Array of entity reference fields in mapping.
   *
   * @var array
   */
  protected $entityReferenceFields;

  /**
   * Array of entity reference fields in mapping.
   *
   * @var array
   */
  protected $entityReferenceFieldsOptions;

  /**
   * MappingSteps constructor.
   *
   * @param \Drupal\gathercontent\Entity\MappingInterface $mapping
   *   Mapping object.
   * @param \Cheppers\GatherContent\DataTypes\Template $template
   *   Template object.
   */
  public function __construct(MappingInterface $mapping, Template $template) {
    $this->mapping = $mapping;
    $this->template = $template;
  }

  /**
   * Sets entityReferenceFields variable.
   *
   * @param array|null $value
   *   Value.
   */
  public function setEntityReferenceFields($value) {
    $this->entityReferenceFields = $value;
  }

  /**
   * Sets entityReferenceFieldsOptions variable.
   *
   * @param array|null $value
   *   Value.
   */
  public function setEntityReferenceFieldsOptions($value) {
    $this->entityReferenceFieldsOptions = $value;
  }

  /**
   * Gets entityReferenceFields variable.
   */
  public function getEntityReferenceFields() {
    return $this->entityReferenceFields;
  }

  /**
   * Gets entityReferenceFieldsOptions variable.
   */
  public function getEntityReferenceFieldsOptions() {
    return $this->entityReferenceFieldsOptions;
  }

  /**
   * Returns form array.
   *
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   Form state object.
   *
   * @return mixed
   *   Return form array.
   */
  public function getForm(FormStateInterface $formState) {
    $form['form_description'] = [
      '#type' => 'html_tag',
      '#tag' => 'i',
      '#value' => t('Please map your GatherContent Template fields to your Drupal
      Content Type Fields. Please note that a GatherContent field can only be
      mapped to a single Drupal field. So each field can only be mapped to once.'),
    ];

    $form['gathercontent_project'] = [
      '#type' => 'item',
      '#title' => t('Project name:'),
      '#markup' => $this->mapping->getGathercontentProject(),
      '#wrapper_attributes' => [
        'class' => [
          'inline-label',
        ],
      ],
    ];

    $form['gathercontent'] = [
      '#type' => 'container',
    ];

    $form['gathercontent']['gathercontent_template'] = [
      '#type' => 'item',
      '#title' => t('GatherContent template:'),
      '#markup' => $this->mapping->getGathercontentTemplate(),
      '#wrapper_attributes' => [
        'class' => [
          'inline-label',
        ],
      ],
    ];

    return $form;
  }

  /**
   * Do validation.
   */
  public function doValidate(array &$form, FormStateInterface $formState) {
    $form_definition_elements = [
      'return',
      'form_build_id',
      'form_token',
      'form_id',
      'op',
    ];
    $non_data_elements = array_merge($form_definition_elements, [
      'content_type',
      'id',
      'updated',
      'gathercontent_project',
      'gathercontent_template',
      'er_mapping_type',
      'submit',
      'close',
    ]);

    $mapping_data = [];
    foreach ($formState->getValues() as $key => $value) {
      if (!in_array($key, $non_data_elements)) {
        $mapping_data[$key] = $value;
      }
    }
    // Check if is translatable.
    $content_type = (empty($this->mapping->getContentType()) ? $formState->getValue('content_type') : $this->mapping->getContentType());
    $translatable = \Drupal::moduleHandler()->moduleExists('content_translation')
      && \Drupal::service('content_translation.manager')
        ->isEnabled('node', $content_type);
    // Validate if each language is used only once
    // for translatable content types.
    $content_lang = [];
    $metatag_lang = [];

    if ($translatable) {
      foreach ($mapping_data as $tab_id => $tab) {
        $tab_type = (isset($tab['type']) ? $tab['type'] : 'content');
        if ($tab['language'] != 'und') {
          if (!in_array($tab['language'], ${$tab_type . '_lang'})) {
            ${$tab_type . '_lang'}[] = $tab['language'];
          }
          else {
            $element = $tab_id . '[language]';
            $formState->setErrorByName($element, $this->t('Each language can be used only once'));
          }
        }
      }
    }

    // Validate if each field is used only once.
    $content_fields = [];
    $metatag_fields = [];

    if ($translatable) {
      foreach ($content_lang as $lang) {
        $content_fields[$lang] = [];
      }
      foreach ($metatag_lang as $lang) {
        $metatag_fields[$lang] = [];
      }
      $content_fields['und'] = $metatag_fields['und'] = [];
    }

    foreach ($mapping_data as $tab_id => $tab) {
      $tab_type = (isset($tab['type']) ? $tab['type'] : 'content');

      if (isset($tab['elements'])) {
        foreach ($tab['elements'] as $k => $element) {
          if (empty($element)) {
            continue;
          }

          if ($translatable) {
            if (
              ($tab_type == 'content' &&
                in_array($this->template->config[$tab_id]->elements[$k]->type, ['text', 'section'])
              ) ||
              !in_array($element, ${$tab_type . '_fields'}[$tab['language']])
            ) {
              ${$tab_type . '_fields'}[$tab['language']][] = $element;
            }
            else {
              if (!strpos($element, '||')) {
                $formState->setErrorByName($tab_id,
                  $this->t('A GatherContent field can only be mapped to a single Drupal field. So each field can only be mapped to once.'));
              }
            }
          }
          else {
            if (
              ($tab_type == 'content' &&
                in_array($this->template->config[$tab_id]->elements[$k]->type, ['text', 'section'])
              ) ||
              !in_array($element, ${$tab_type . '_fields'})
            ) {
              ${$tab_type . '_fields'}[] = $element;
            }
            else {
              if (!strpos($element, '||')) {
                $formState->setErrorByName($tab_id,
                  $this->t('A GatherContent field can only be mapped to a single Drupal field. So each field can only be mapped to once.'));
              }
            }
          }
        }
      }
    }

    // Validate if at least one field in mapped.
    if (!$translatable && empty($content_fields) && empty($metatag_fields)) {
      $formState->setErrorByName('form', t('You need to map at least one field to create mapping.'));
    }
    elseif ($translatable &&
      count($content_fields) === 1
      && empty($content_fields['und'])
      && empty($metatag_fields['und'])
      && count($metatag_fields) === 1
    ) {
      $formState->setErrorByName('form', t('You need to map at least one field to create mapping.'));
    }

    // Validate if title is mapped for translatable content.
    if ($translatable) {
      foreach ($content_fields as $k => $lang_fields) {
        if (!in_array('title', $lang_fields) && $k != 'und') {
          $formState->setErrorByName('form', t('You have to map Drupal Title field for translatable content.'));
        }
      }
    }
  }

  /**
   * Wrapper function for filterFieldsRecursively.
   *
   * Use for filtering only equivalent fields.
   *
   * @param \Cheppers\GatherContent\DataTypes\Element $gc_field
   *   Type of field in GatherContent.
   * @param string $content_type
   *   Name of Drupal content type.
   *
   * @return array
   *   Associative array with equivalent fields.
   */
  protected function filterFields(Element $gc_field, $content_type) {
    $fields = $this->filterFieldsRecursively($gc_field, $content_type);

    if ($gc_field->type === 'text' &&
      $gc_field instanceof ElementText &&
      $gc_field->plainText
    ) {
      $fields['title'] = 'Title';
    }

    return $fields;
  }

  /**
   * Helper function.
   *
   * Use for filtering only equivalent fields.
   *
   * @param object $gc_field
   *   Type of field in GatherContent.
   * @param string $content_type
   *   Name of Drupal content type.
   * @param string $entity_type
   *   Name of Drupal Entity type.
   * @param array $nested_ids
   *   Nested ID array.
   * @param string $bundle_label
   *   Bundle label string.
   *
   * @return array
   *   Associative array with equivalent fields.
   */
  protected function filterFieldsRecursively($gc_field, $content_type, $entity_type = 'node', array $nested_ids = [], $bundle_label = '') {
    $mapping_array = [
      'files' => [
        'file',
        'image',
        'entity_reference_revisions',
      ],
      'section' => [
        'text_long',
        'entity_reference_revisions',
      ],
      'text' => [
        'text',
        'text_long',
        'text_with_summary',
        'string_long',
        'string',
        'email',
        'telephone',
        'date',
        'datetime',
        'entity_reference_revisions',
      ],
      'choice_radio' => [
        'string',
        'entity_reference',
        'entity_reference_revisions',
      ],
      'choice_checkbox' => [
        'list_string',
        'entity_reference',
        'entity_reference_revisions',
      ],
    ];
    $entityFieldManager = \Drupal::service('entity_field.manager');
    $entityTypeManager = \Drupal::entityTypeManager();

    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $instances */
    $instances = $entityFieldManager->getFieldDefinitions($entity_type, $content_type);

    $fields = [];
    // Fields.
    foreach ($instances as $name => $instance) {
      if ($instance instanceof BaseFieldDefinition) {
        continue;
      }
      if (in_array($instance->getType(), $mapping_array[$gc_field->type])) {
        // Constrains:
        // - do not map plain text (Drupal) to rich text (GC).
        // - do not map radios (GC) to text (Drupal),
        // if widget isn't provided by select_or_other module.
        // - do not map section (GC) to plain text (Drupal).
        // - map only taxonomy entity reference (Drupal) to radios
        // and checkboxes (GC).
        switch ($gc_field->type) {
          case 'text':
            if (
              (!isset($gc_field->plainText) || !$gc_field->plainText) &&
              in_array($instance->getType(), [
                'string',
                'string_long',
                'email',
                'telephone',
              ])
            ) {
              continue 2;
            }
            break;

          case 'section':
            if (in_array($instance->getType(), [
              'string',
              'string_long',
            ])) {
              continue 2;
            }
            break;

          case 'choice_radio':
          case 'choice_checkbox':
            if (
              $instance->getType() !== 'entity_reference_revisions' &&
              $instance->getSetting('handler') !== 'default:taxonomy_term'
            ) {
              continue 2;
            }
            break;
        }

        if ($instance->getType() === 'entity_reference_revisions') {
          $settings = $instance->getSetting('handler_settings');

          if (!empty($settings['target_bundles'])) {
            $bundles = $settings['target_bundles'];
            if (!empty($settings['negate']) && !empty($settings['target_bundles_drag_drop'])) {
              $negated_bundles = array_filter(
                $settings['target_bundles_drag_drop'],
                function ($v) {
                  return !$v['enabled'];
                }
              );

              $bundles = array_combine(array_keys($negated_bundles), array_keys($negated_bundles));
            }
            $target_type = $instance->getFieldStorageDefinition()
              ->getSetting('target_type');
            $bundle_entity_type = $entityTypeManager
              ->getStorage($target_type)
              ->getEntityType()
              ->get('bundle_entity_type');

            $new_nested_ids = $nested_ids;
            $new_nested_ids[] = $instance->id();

            foreach ($bundles as $bundle) {
              $new_bundle_label = ((!empty($bundle_label)) ? $bundle_label . ' - ' : '') . $instance->getLabel();
              $bundle_name = $entityTypeManager
                ->getStorage($bundle_entity_type)
                ->load($bundle)
                ->label();

              $new_bundle_label .= ' (bundle: ' . $bundle_name . ')';

              $targetFields = $this->filterFieldsRecursively($gc_field, $bundle, $target_type, $new_nested_ids, $new_bundle_label);

              if (!empty($targetFields)) {
                $fields = $fields + $targetFields;
              }
            }
          }
        }
        else {
          $key = $instance->id();

          if (!empty($nested_ids)) {
            $new_nested_ids = $nested_ids;
            $new_nested_ids[] = $instance->id();
            $key = implode('||', $new_nested_ids);
          }

          $fields[$key] = ((!empty($bundle_label)) ? $bundle_label . ' - ' : '') . $instance->getLabel();

          if ($instance->getType() === 'entity_reference' && $instance->getSetting('handler') === 'default:taxonomy_term') {
            $mappingData = unserialize($this->mapping->getData());

            if ($mappingData) {
              foreach ($mappingData as $tabName => $tab) {
                $gcField = array_search($key, $tab['elements']);
                if (empty($gcField)) {
                  continue;
                }
                if (isset($tab['language'])) {
                  $this->entityReferenceFields[$key][$tab['language']]['name'] = $gcField;
                  $this->entityReferenceFields[$key][$tab['language']]['tab'] = $tabName;
                }
                else {
                  $this->entityReferenceFields[$key][LanguageInterface::LANGCODE_NOT_SPECIFIED]['name'] = $gcField;
                  $this->entityReferenceFields[$key][LanguageInterface::LANGCODE_NOT_SPECIFIED]['tab'] = $tabName;
                }
              }
            }

            if (empty($this->entityReferenceFieldsOptions) || !in_array($key, $this->entityReferenceFieldsOptions)) {
              $this->entityReferenceFieldsOptions[] = $key;
            }
          }
        }
      }
    }

    return $fields;
  }

  /**
   * Return only supported metatag fields.
   *
   * @param object $gathercontent_field
   *   Object of field from GatherContent.
   *
   * @return array
   *   Array of supported metatag fields.
   */
  protected function filterMetatags($gathercontent_field) {
    if (
      $gathercontent_field->type === 'text' &&
      isset($gathercontent_field->plainText) &&
      $gathercontent_field->plainText
    ) {
      return [
        'title' => t('Title'),
        'description' => t('Description'),
        'abstract' => t('Abstract'),
        'keywords' => t('Keywords'),
      ];
    }

    else {
      return [];
    }
  }

  /**
   * Get list of languages as assoc array.
   *
   * @return array
   *   Assoc array of languages keyed by lang code, value is language name.
   */
  protected function getLanguageList() {
    $languages = \Drupal::service('language_manager')
      ->getLanguages(LanguageInterface::STATE_CONFIGURABLE);
    $language_list = [];
    foreach ($languages as $lang_code => $language) {
      /** @var \Drupal\Core\Language\Language $language */
      $language_list[$lang_code] = $language->getName();
    }
    return $language_list;
  }

}
