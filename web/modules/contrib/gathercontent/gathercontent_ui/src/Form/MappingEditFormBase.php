<?php

namespace Drupal\gathercontent_ui\Form;

use Cheppers\GatherContent\DataTypes\Template;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Class MappingEditFormBase.
 *
 * @package Drupal\gathercontent_ui\Form
 */
class MappingEditFormBase extends EntityForm {

  /**
   * Flag for mapping if it's new.
   *
   * @var bool
   */
  protected $new;

  /**
   * Step in multipart form.
   *
   * Values:
   * - field_mapping
   * - er_mapping
   * - completed.
   *
   * @var string
   */
  protected $step;

  /**
   * Mapping data.
   *
   * @var array
   */
  protected $mappingData;

  /**
   * GatherContent full template.
   *
   * @var object
   */
  protected $template;

  /**
   * Machine name of content type.
   *
   * @var string
   */
  protected $contentType;

  /**
   * Type of import for entity reference fields.
   *
   * Values:
   * - automatic
   * - manual
   * - semiautomatic.
   *
   * @var string
   */
  protected $erImportType;

  /**
   * Flag for skipping ER mapping.
   *
   * @var bool
   */
  protected $skip;

  /**
   * Count of imported or updated taxonomy terms.
   *
   * @var int
   */
  protected $erImported;

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
   * Ajax callback for mapping multistep form.
   *
   * @return array
   *   Array of form elements.
   *
   * @inheritdoc
   */
  public function getMappingTable(array &$form, FormStateInterface $form_state) {
    $this->contentType = $form_state->getValue('content_type');
    $fields = $this->entityReferenceFieldsOptions;
    $form['mapping']['#attached']['drupalSettings']['gathercontent'] = (empty($fields) ? NULL : $fields);
    $form_state->setRebuild(TRUE);
    return $form['mapping'];
  }

  /**
   * Generate automatically terms for local field from GatherContent options.
   *
   * @param \Drupal\field\Entity\FieldConfig $handlerSettings
   *   Field config for local field.
   * @param array $localOptions
   *   Array of remote options.
   * @param string $langcode
   *   The language of the generated term.
   */
  public function automaticTermsGenerator(FieldConfig $handlerSettings, array $localOptions, $langcode) {
    $settings = $handlerSettings->getSetting('handler_settings');
    /** @var \Drupal\taxonomy\Entity\Term[] $terms */
    if (!empty($settings['auto_create_bundle'])) {
      $vid = $settings['auto_create_bundle'];
    }
    else {
      $vid = reset($settings['target_bundles']);
    }

    // Check if field exists.
    $this->gcOptionIdsFieldExists($vid);

    foreach ($localOptions as $id => $localOption) {
      $query = \Drupal::entityQuery('taxonomy_term');
      $group = $query->orConditionGroup()
        ->condition('gathercontent_option_ids', $id)
        ->condition('name', $localOption);
      $term_ids = $query->condition($group)
        ->condition('vid', $vid)
        ->condition('langcode', $langcode)
        ->execute();
      $term_id = array_shift($term_ids);
      if (!empty($term_id)) {
        $term = Term::load($term_id);
        if ($langcode === LanguageInterface::LANGCODE_NOT_SPECIFIED) {
          if ($term->label() !== $localOption) {
            $term->setName($localOption);
          }
          $values = $term->get('gathercontent_option_ids')->getValue();
          $mappedValues = array_map(function ($array) {
            return $array['value'];
          }, $values);

          if (!in_array($id, $mappedValues)) {
            $term->gathercontent_option_ids->appendItem($id);
          }
        }
        else {
          if ($term->getTranslation($langcode)->label() !== $localOption) {
            $term->getTranslation($langcode)->setName($localOption);
          }
          $values = $term->getTranslation($langcode)->get('gathercontent_option_ids')->getValue();
          $mappedValues = array_map(function ($array) {
            return $array['value'];
          }, $values);

          if (!in_array($id, $mappedValues)) {
            $term->getTranslation($langcode)->gathercontent_option_ids->appendItem($id);
          }
        }

        $term->save();
        $this->erImported++;
      }
      else {
        $term_values = [
          'vid' => $vid,
          'langcode' => $langcode,
        ];
        $term = Term::create($term_values);

        $term->setName($localOption);
        $term->set('gathercontent_option_ids', $id);
        $term->save();
        $this->erImported++;
      }
    }
  }

  /**
   * Prepare options for every language for every field.
   *
   * @param \Cheppers\GatherContent\DataTypes\Template $template
   *   GatherContent template object.
   *
   * @return array
   *   Array with options.
   */
  public function prepareOptions(Template $template) {
    $options = [];
    foreach ($this->entityReferenceFields as $field => $gcMapping) {
      foreach ($gcMapping as $lang => $fieldSettings) {
        foreach ($template->config as $tab) {
          if ($tab->id === $fieldSettings['tab']) {
            foreach ($tab->elements as $element) {
              if ($element->id === $fieldSettings['name']) {
                foreach ($element->options as $option) {
                  if (!isset($option['value'])) {
                    $options[$option['name']] = $option['label'];
                  }
                }
              }
            }
          }
        }
      }
    }
    return $options;
  }

  /**
   * Validate if gathercontent_option_ids field exists on specified vocabulary.
   *
   * If field doesn't exists, create it for specified vocabulary.
   *
   * @param string $vid
   *   Taxonomy vocabulary identifier.
   */
  public function gcOptionIdsFieldExists($vid) {
    if ($this->entityTypeManager->hasDefinition('taxonomy_term')) {
      $entityFieldManager = \Drupal::service('entity_field.manager');
      $definitions = $entityFieldManager->getFieldStorageDefinitions('taxonomy_term');
      if (!isset($definitions['gathercontent_option_ids'])) {
        FieldStorageConfig::create([
          'field_name' => 'gathercontent_option_ids',
          'entity_type' => 'taxonomy_term',
          'type' => 'string',
          'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
          'locked' => TRUE,
          'persist_with_no_fields' => TRUE,
          'settings' => [
            'is_ascii' => FALSE,
            'case_sensitive' => FALSE,
          ],
        ])->save();
      }

      $field_config = FieldConfig::loadByName('taxonomy_term', $vid, 'gathercontent_option_ids');
      if (is_null($field_config)) {
        FieldConfig::create([
          'field_name' => 'gathercontent_option_ids',
          'entity_type' => 'taxonomy_term',
          'bundle' => $vid,
          'label' => 'GatherContent Option IDs',
        ])->save();
      }
    }
  }

  /**
   * Handle manual type of taxonomy terms.
   *
   * @param array|null $languages
   *   Array with languages available for mapping.
   * @param \Drupal\Core\Entity\EntityStorageInterface $entityStorage
   *   Storage object for taxonomy terms.
   * @param array $row
   *   Array with mapping options.
   */
  public function manualErImport($languages, EntityStorageInterface $entityStorage, array $row) {
    if (!empty($languages) && !empty($row['terms'])) {
      $terms = $entityStorage->loadByProperties(['gathercontent_option_ids' => $row[$languages[0]]]);
      /** @var \Drupal\taxonomy\Entity\Term $term */
      $term = array_shift($terms);
      // If term already exists.
      if (!empty($term)) {
        // If term was changed, remove option ids for every
        // language.
        if ($term->id() !== $row['terms']) {
          // We don't know how many languages are translated.
          $translation_languages = $term->getTranslationLanguages(TRUE);
          foreach ($translation_languages as $language) {
            if ($term->hasTranslation($language) && !empty($row[$language])) {
              $option_ids = $term->getTranslation($language)
                ->get('gathercontent_option_ids');
              foreach ($option_ids as $i => $option_id) {
                if ($option_id == $row[$language]) {
                  unset($option_ids[$i]);
                }
              }
              $term->getTranslation($language)
                ->set('gathercontent_option_ids', $option_ids);
            }
          }
        }
      }

      // Set new values to correct term.
      $term = Term::load($row['terms']);
      if (!empty($languages)) {
        foreach ($languages as $language) {
          $term->getTranslation($language)
            ->set('gathercontent_option_ids', $row[$language]);
        }
      }
      $term->save();
      $this->erImported++;
    }
    elseif (empty($languages) && !empty($row['terms'])) {
      $und_lang_value = $row[LanguageInterface::LANGCODE_NOT_SPECIFIED];
      if (!empty($und_lang_value)) {
        $terms = $entityStorage->loadByProperties(['gathercontent_option_ids' => $und_lang_value]);
        /** @var \Drupal\taxonomy\Entity\Term $term */
        $term = array_shift($terms);
        // If term already exists.
        if (!empty($term)) {
          // If term was changed, remove option ids for every
          // language.
          if ($term->id() !== $row['terms']) {
            $option_ids = $term->get('gathercontent_option_ids');
            foreach ($option_ids as $i => $option_id) {
              if ($option_id == $und_lang_value) {
                unset($option_ids[$i]);
              }
            }
            $term->set('gathercontent_option_ids', $option_ids);
          }
        }
        // Set new values to correct term.
        $term = Term::load($row['terms']);
        $term->set('gathercontent_option_ids', $und_lang_value);
        $term->save();
        $this->erImported++;
      }
    }
  }

  /**
   * Handle semiautomatic import of taxonomy terms.
   *
   * @param array|null $languages
   *   Array with languages available for mapping.
   * @param \Drupal\Core\Entity\EntityStorageInterface $entityStorage
   *   Storage object for taxonomy terms.
   * @param array $row
   *   Array with mapping options.
   * @param array $options
   *   GatherContent options for every language and every field.
   * @param string $vid
   *   Taxonomy vocabulry identifier.
   */
  public function semiErImport($languages, EntityStorageInterface $entityStorage, array $row, array $options, $vid) {
    if (!empty($languages)) {
      $terms = $entityStorage->loadByProperties(['gathercontent_option_ids' => $row[$languages[0]]]);
      /** @var \Drupal\taxonomy\Entity\Term $term */
      $term = array_shift($terms);
      if (!empty($term)) {
        foreach ($languages as $language) {
          if (!empty($row[$language]) && $term->hasTranslation($language) && $term->getTranslation($language)->label() !== $options[$row[$language]]) {
            $term->getTranslation($language)
              ->setName($options[$row[$language]]);
          }
        }
        $term->save();
        $this->erImported++;
      }
      else {
        $term = Term::create([
          'vid' => $vid,
        ]);
        foreach ($languages as $language) {
          if (!empty($row[$language])) {
            if (!$term->hasTranslation($language)) {
              $term->addTranslation($language);
            }
            $term->getTranslation($language)
              ->set('gathercontent_option_ids', $row[$language]);
            $term->getTranslation($language)
              ->setName($options[$row[$language]]);
          }
        }
        if (!empty($term->getTranslationLanguages())) {
          $term->save();
          $this->erImported++;
        }
      }
    }
    else {
      /** @var \Drupal\taxonomy\Entity\Term $term */
      $und_lang_value = $row[LanguageInterface::LANGCODE_NOT_SPECIFIED];
      if (!empty($und_lang_value)) {
        $terms = $entityStorage->loadByProperties(['gathercontent_option_ids' => $und_lang_value]);
        $term = array_shift($terms);
        if (!empty($term)) {
          if ($term->label() !== $options[$und_lang_value]) {
            $term->setName($options[$und_lang_value]);
          }
          $term->save();
          $this->erImported++;
        }
        else {
          $term = Term::create([
            'vid' => $vid,
            'gathercontent_option_ids' => $und_lang_value,
          ]);
          $term->setName($options[$und_lang_value]);
          $term->save();
          $this->erImported++;
        }
      }
    }
  }

  /**
   * Get available languages from currect row.
   *
   * @param array $row
   *   Currect row from mapping.
   *
   * @return array
   *   Array with available languages.
   */
  public function getAvailableLanguages(array $row) {
    $languages = array_keys($row);

    foreach ($languages as $i => $language) {
      if ($language === 'und') {
        unset($languages[$i]);
      }
      elseif ($language === 'terms') {
        unset($languages[$i]);
      }
    }
    return $languages;
  }

  /**
   * Get vocabulary identifier for field in content type.
   *
   * @param string $field_id
   *   ID of local field.
   *
   * @return string
   *   Identifier of vocabulary.
   */
  public function getVocabularyId($field_id) {
    // Load vocabulary.
    $id_array = explode('||', $field_id);
    $field_config = FieldConfig::load($id_array[count($id_array) - 1]);
    $settings = $field_config->getSetting('handler_settings');
    /** @var \Drupal\taxonomy\Entity\Term[] $terms */
    if (!empty($settings['auto_create_bundle'])) {
      $vid = $settings['auto_create_bundle'];
      return $vid;
    }
    else {
      $vid = reset($settings['target_bundles']);
      return $vid;
    }
  }

  /**
   * Extract mapping data from submitted form values.
   *
   * @param array $formValues
   *   Array with all submitted values.
   *
   * @return array
   *   Mapping data.
   */
  public function extractMappingData(array $formValues) {
    $form_definition_elements = [
      'return',
      'form_build_id',
      'form_token',
      'form_id',
      'op',
    ];
    $non_data_elements = array_merge($form_definition_elements, [
      'gc_template',
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
    foreach ($formValues as $key => $value) {
      if (!in_array($key, $non_data_elements)) {
        $mapping_data[$key] = $value;
      }
    }

    $this->mappingData = $mapping_data;
    return $mapping_data;
  }

}
