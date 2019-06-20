<?php

namespace Drupal\gathercontent\Import\ContentProcess;

use Cheppers\GatherContent\DataTypes\Item;
use Cheppers\GatherContent\GatherContentClientInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Render\PlainTextOutput;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\gathercontent\Entity\Mapping;
use Drupal\gathercontent\Import\Importer;
use Drupal\gathercontent\Import\ImportOptions;
use Drupal\gathercontent\Import\NodeUpdateMethod;
use Drupal\gathercontent\MappingLoader;
use Drupal\gathercontent\MetatagQuery;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The ContentProcessor sets the necessary fields of the entity.
 */
class ContentProcessor implements ContainerInjectionInterface {

  /**
   * Drupal GC client.
   *
   * @var \Drupal\gathercontent\DrupalGatherContentClient
   */
  protected $client;

  /**
   * Store the already imported entity references (used in recursion).
   *
   * @var array
   */
  protected $importedReferences = [];

  /**
   * Meta tag query object.
   *
   * @var \Drupal\gathercontent\MetatagQuery
   */
  protected $metatag;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The time service.
   *
   * @var array
   */
  protected $concatFieldValues = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(
    GatherContentClientInterface $client,
    MetatagQuery $metatag,
    TimeInterface $time
  ) {
    $this->client = $client;
    $this->metatag = $metatag;
    $this->time = $time;
    $this->init();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('gathercontent.client'),
      $container->get('gathercontent.metatag'),
      $container->get('datetime.time')
    );
  }

  /**
   * Initialize member variables.
   */
  public function init() {
    $this->importedReferences = [];
  }

  /**
   * Create a Drupal node filled with the properties of the GC item.
   */
  public function createNode(Item $gc_item, ImportOptions $options, array $files) {
    $this->concatFieldValues = [];

    $mapping = MappingLoader::load($gc_item);
    $content_type = $mapping->getContentType();
    $is_translatable = Importer::isContentTypeTranslatable($content_type);
    $mapping_data = unserialize($mapping->getData());

    if (empty($mapping_data)) {
      throw new \Exception("Mapping data is empty.");
    }

    $first = reset($mapping_data);
    $langcode = isset($first['language']) ? $first['language'] : Language::LANGCODE_NOT_SPECIFIED;

    // Create a Drupal entity corresponding to GC item.
    $entity = NodeUpdateMethod::getDestinationNode($gc_item->id, $options->getNodeUpdateMethod(), $content_type, $langcode);

    if ($entity === FALSE) {
      throw new \Exception("System error, please contact you administrator.");
    }

    // Create new revision according to the import options.
    if (
      $entity->getEntityType()->isRevisionable() &&
      !$entity->isNew() &&
      $options->getCreateNewRevision()
    ) {
      $entity->setNewRevision(TRUE);
      $entity->setRevisionLogMessage('Created revision for node ID: ' . $entity->id());
      $entity->setRevisionCreationTime($this->time->getRequestTime());
      $entity->setRevisionUserId(\Drupal::currentUser()->id());
    }

    $entity->set('gc_id', $gc_item->id);
    $entity->set('gc_mapping_id', $mapping->id());
    $entity->setOwnerId(\Drupal::currentUser()->id());

    if ($entity->isNew()) {
      $entity->setPublished($options->getPublish());
    }

    foreach ($gc_item->config as $pane) {
      $is_pane_translatable = $is_translatable && isset($mapping_data[$pane->id]['language'])
        && ($mapping_data[$pane->id]['language'] != Language::LANGCODE_NOT_SPECIFIED);

      if ($is_pane_translatable) {
        $language = $mapping_data[$pane->id]['language'];
        if (!$entity->hasTranslation($language)) {
          $entity->addTranslation($language);
          if ($entity->isNew()) {
            $entity->getTranslation($language)->setPublished($options->getPublish());
          }
        }
      }
      else {
        $language = Language::LANGCODE_NOT_SPECIFIED;
      }

      foreach ($pane->elements as $field) {
        if (isset($mapping_data[$pane->id]['elements'][$field->id]) && !empty($mapping_data[$pane->id]['elements'][$field->id])) {
          $local_field_id = $mapping_data[$pane->id]['elements'][$field->id];
          $local_field_text_format = '';

          if (
            isset($mapping_data[$pane->id]['element_text_formats']) &&
            !empty($mapping_data[$pane->id]['element_text_formats'][$field->id])
          ) {
            $local_field_text_format = $mapping_data[$pane->id]['element_text_formats'][$field->id];
          }

          if (isset($mapping_data[$pane->id]['type']) && ($mapping_data[$pane->id]['type'] === 'content') || !isset($mapping_data[$pane->id]['type'])) {
            $this->processContentPane($entity, $local_field_id, $field, $is_pane_translatable, $language, $files, $local_field_text_format);
          }
          elseif (isset($mapping_data[$pane->id]['type']) && ($mapping_data[$pane->id]['type'] === 'metatag')) {
            $this->processMetatagPane($entity, $local_field_id, $field, $mapping->getContentType(), $is_pane_translatable, $language);
          }
        }
      }
    }

    if (!$is_translatable && empty($entity->getTitle())) {
      $entity->setTitle($gc_item->name);
    }

    return $entity;
  }

  /**
   * Processing function for content panes.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Object of node.
   * @param string $local_field_id
   *   ID of local Drupal field.
   * @param object $field
   *   Object of GatherContent field.
   * @param bool $is_translatable
   *   Indicator if node is translatable.
   * @param string $language
   *   Language of translation if applicable.
   * @param array $files
   *   Array of files fetched from GatherContent.
   * @param string $local_field_text_format
   *   Text format setting for the local drupal field.
   * @param string $parent_field_type
   *   Parent field type string to pass through field type
   *   in case of reference fields.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Exception
   */
  public function processContentPane(EntityInterface &$entity, $local_field_id, $field, $is_translatable, $language, array $files, $local_field_text_format, $parent_field_type = '') {
    if (empty(trim($field->value))) {
      return;
    }

    $local_id_array = explode('||', $local_field_id);

    if (count($local_id_array) > 1) {
      $entityTypeManager = \Drupal::entityTypeManager();
      $field_info = FieldConfig::load($local_id_array[0]);
      $field_target_info = FieldConfig::load($local_id_array[1]);
      $field_name = $field_info->getName();

      $entityStorage = $entityTypeManager
        ->getStorage($field_target_info->getTargetEntityTypeId());

      $target_field_value = $entity
        ->getTranslation(Language::LANGCODE_DEFAULT)
        ->get($field_name)
        ->getValue();

      if (!isset($this->importedReferences[$local_id_array[0]])) {
        if (!empty($target_field_value)) {
          foreach ($target_field_value as $target) {
            $deleteEntity = $entityStorage->load($target['target_id']);

            if ($deleteEntity) {
              $deleteEntity->delete();
            }
          }
        }

        $this->importedReferences[$local_id_array[0]] = TRUE;
        $target_field_value = [];
      }

      array_shift($local_id_array);
      $to_import = TRUE;

      if (!empty($target_field_value)) {
        foreach ($target_field_value as $target) {
          $childEntity = $entityStorage->loadByProperties([
            'id' => $target['target_id'],
            'type' => $field_target_info->getTargetBundle(),
          ]);

          if (!empty($childEntity[$target['target_id']])) {
            $check_field_name = $field_target_info->getName();
            $check_field_value = $childEntity[$target['target_id']]
              ->get($check_field_name)
              ->getValue();

            if ($is_translatable) {
              if (!$childEntity[$target['target_id']]->hasTranslation($language)) {
                $childEntity[$target['target_id']]->addTranslation($language);
              }

              if ($childEntity[$target['target_id']]->hasTranslation($language)) {
                $check_field_value = $childEntity[$target['target_id']]
                  ->getTranslation($language)
                  ->get($check_field_name)
                  ->getValue();
              }
            }

            if (count($local_id_array) > 1 || empty($check_field_value)) {
              $this->processContentPane($childEntity[$target['target_id']],
                implode('||', $local_id_array), $field, $is_translatable,
                $language, $files, $local_field_text_format, $field_info->getType());

              $childEntity[$target['target_id']]->save();
              $to_import = FALSE;
            }
          }
        }
      }

      if ($to_import) {
        $childEntity = $entityStorage->create([
          'type' => $field_target_info->getTargetBundle(),
        ]);

        $this->processContentPane($childEntity, implode('||', $local_id_array),
          $field, $is_translatable, $language, $files, $local_field_text_format, $field_info->getType());

        $childEntity->save();

        $target_field_value[] = [
          'target_id' => $childEntity->id(),
          'target_revision_id' => $childEntity->getRevisionId(),
        ];
      }

      $entity
        ->getTranslation(Language::LANGCODE_DEFAULT)
        ->set($field_name, $target_field_value);
    }
    else {
      $field_info = FieldConfig::load($local_field_id);

      if (!is_null($field_info)) {
        $is_translatable = $is_translatable && $field_info->isTranslatable();
      }

      if ($local_field_id === 'title') {
        $target = &$entity;
        if ($is_translatable) {
          $target = $entity->getTranslation($language);
          if (empty($field->value)) {
            throw new \Exception(
              "Field '{$field->label}' must not be empty (it's a title field in a translatable item)."
            );
          }
        }
        $target->setTitle($field->value);
        return;
      }

      switch ($field->type) {
        case 'files':
          $this->processFilesField($entity, $field_info, $field->id,
            $is_translatable, $language, $files);
          break;

        case 'choice_radio':
          $this->processChoiceRadioField($entity, $field_info, $is_translatable,
            $language, $field->options);
          break;

        case 'choice_checkbox':
          $this->processChoiceCheckboxField($entity, $field_info,
            $is_translatable, $language, $field->options);
          break;

        case 'section':
          $this->processSectionField($entity, $field_info, $is_translatable,
            $language, $field, $local_field_text_format, $parent_field_type);
          break;

        default:
          $this->processDefaultField($entity, $field_info, $is_translatable,
            $language, $field, $local_field_text_format, $parent_field_type);
          break;
      }
    }
  }

  /**
   * Processing function for metatag panes.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   Object of node.
   * @param string $local_field_name
   *   ID of local Drupal field.
   * @param object $field
   *   Object of GatherContent field.
   * @param string $content_type
   *   Name of Content type, we are mapping to.
   * @param bool $is_translatable
   *   Indicator if node is translatable.
   * @param string $language
   *   Language of translation if applicable.
   *
   * @throws \Exception
   *   If content save fails, exceptions is thrown.
   */
  public function processMetatagPane(NodeInterface &$entity, $local_field_name, $field, $content_type, $is_translatable, $language) {
    if (\Drupal::moduleHandler()->moduleExists('metatag') && $this->metatag->checkMetatag($content_type)) {
      $metatag_fields = $this->metatag->getMetatagFields($content_type);

      foreach ($metatag_fields as $metatag_field) {
        if ($is_translatable) {
          $current_value = unserialize($entity->getTranslation($language)->{$metatag_field}->value);
          $current_value[$local_field_name] = $field->value;
          $entity->getTranslation($language)->{$metatag_field}->value = serialize($current_value);
        }
        else {
          $current_value = unserialize($entity->{$metatag_field}->value);
          $current_value[$local_field_name] = $field->value;
          $entity->{$metatag_field}->value = serialize($current_value);
        }
      }
    }
    else {
      throw new \Exception("Metatag module not enabled or entity doesn't support
    metatags while trying to map values with metatag content.");
    }
  }

  /**
   * Default processing function, when no other matches found, usually for text.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Object of node.
   * @param \Drupal\field\Entity\FieldConfig $field_info
   *   Local field Info object.
   * @param bool $is_translatable
   *   Indicator if node is translatable.
   * @param string $language
   *   Language of translation if applicable.
   * @param object $field
   *   Object with field attributes.
   * @param string $text_format
   *   Text format string.
   * @param string $parent_field_type
   *   Parent field type string to pass through field type
   *   in case of reference fields.
   */
  protected function processDefaultField(EntityInterface &$entity, FieldConfig $field_info, $is_translatable, $language, $field, $text_format, $parent_field_type = '') {
    $local_field_name = $field_info->getName();
    $value = $field->value;
    $target = &$entity;

    if ($is_translatable) {
      $target = $entity->getTranslation($language);
    }

    switch ($field_info->getType()) {
      case 'datetime':
        $value = strtotime($value);
        if ($value === FALSE) {
          // If we failed to convert to a timestamp, abort.
          return;
        }
        $target->{$local_field_name} = [
          'value' => gmdate(DATETIME_DATETIME_STORAGE_FORMAT, $value),
        ];
        break;

      case 'date':
        $value = strtotime($value);
        if ($value === FALSE) {
          return;
        }
        $target->{$local_field_name} = [
          'value' => gmdate(DATETIME_DATE_STORAGE_FORMAT, $value),
        ];
        break;

      default:
        $id = $language . $field_info->id();

        if (
          !isset($this->concatFieldValues[$id]) ||
          $parent_field_type === 'entity_reference_revisions'
        ) {
          $this->concatFieldValues[$id] = '';
        }

        $this->concatFieldValues[$id] .= $value;

        // Probably some kind of text field.
        $target->{$local_field_name} = [
          'value' => $this->concatFieldValues[$id],
          'format' => (isset($field->plainText) && $field->plainText ? 'plain_text' : (!empty($text_format) ? $text_format : 'basic_html')),
        ];
        break;
    }
  }

  /**
   * Processing function for section type of field.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Object of node.
   * @param \Drupal\field\Entity\FieldConfig $field_info
   *   Local field Info object.
   * @param bool $is_translatable
   *   Indicator if node is translatable.
   * @param string $language
   *   Language of translation if applicable.
   * @param object $field
   *   Object with field attributes.
   * @param string $text_format
   *   Text format string.
   * @param string $parent_field_type
   *   Parent field type string to pass through field type
   *   in case of reference fields.
   */
  protected function processSectionField(EntityInterface &$entity, FieldConfig $field_info, $is_translatable, $language, $field, $text_format, $parent_field_type = '') {
    $local_field_name = $field_info->getName();
    $target = &$entity;

    if ($is_translatable) {
      $target = $entity->getTranslation($language);
    }

    $id = $language . $field_info->id();

    if (
      !isset($this->concatFieldValues[$id]) ||
      $parent_field_type === 'entity_reference_revisions'
    ) {
      $this->concatFieldValues[$id] = '';
    }

    $this->concatFieldValues[$id] .= '<h3>' . $field->title . '</h3>' . $field->subtitle;

    $target->{$local_field_name} = [
      'value' => $this->concatFieldValues[$id],
      'format' => (!empty($text_format) ? $text_format : 'basic_html'),
    ];
  }

  /**
   * Processing function for checkbox type of field.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Object of node.
   * @param \Drupal\field\Entity\FieldConfig $field_info
   *   Local field Info object.
   * @param bool $is_translatable
   *   Indicator if node is translatable.
   * @param string $language
   *   Language of translation if applicable.
   * @param array $options
   *   Array of options.
   */
  protected function processChoiceCheckboxField(EntityInterface &$entity, FieldConfig $field_info, $is_translatable, $language, array $options) {
    $local_field_name = $field_info->getName();
    $entity->{$local_field_name} = [NULL];
    $selected_options = [];
    foreach ($options as $option) {
      if ($option['selected']) {
        if ($field_info->getType() === 'entity_reference') {
          if (!empty($field_info->getSetting('handler_settings')['auto_create_bundle'])) {
            $vid = $field_info->getSetting('handler_settings')['auto_create_bundle'];
          }
          else {
            $handler_settings = $field_info->getSetting('handler_settings');
            $handler_settings = reset($handler_settings);
            $vid = array_shift($handler_settings);
          }

          $taxonomy = \Drupal::entityTypeManager()
            ->getStorage('taxonomy_term')
            ->loadByProperties([
              'gathercontent_option_ids' => $option['name'],
              'vid' => $vid,
            ]);

          /** @var \Drupal\taxonomy\Entity\Term $term */
          $term = array_shift($taxonomy);
          $selected_options[] = $term->id();
        }
        else {
          $selected_options[] = $option['name'];
        }
      }
      if ($is_translatable) {
        $entity->getTranslation($language)->{$local_field_name} = $selected_options;
      }
      else {
        $entity->{$local_field_name} = $selected_options;
      }
    }
  }

  /**
   * Processing function for radio type of field.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Object of node.
   * @param \Drupal\field\Entity\FieldConfig $field_info
   *   Local field Info object.
   * @param bool $is_translatable
   *   Indicator if node is translatable.
   * @param string $language
   *   Language of translation if applicable.
   * @param array $options
   *   Array of options.
   */
  protected function processChoiceRadioField(EntityInterface &$entity, FieldConfig $field_info, $is_translatable, $language, array $options) {
    $local_field_name = $field_info->getName();
    foreach ($options as $option) {
      if (!$option['selected']) {
        continue;
      }
      if (isset($option['value'])) {
        if (empty($option['value'])) {
          continue;
        }
        // Dealing with "Other" option.
        if ($field_info->getType() === 'entity_reference') {
          // Load vocabulary id.
          if (!empty($field_info->getSetting('handler_settings')['auto_create_bundle'])) {
            $vid = $field_info->getSetting('handler_settings')['auto_create_bundle'];
          }
          else {
            $handler_settings = $field_info->getSetting('handler_settings');
            $handler_settings = reset($handler_settings);
            $vid = array_shift($handler_settings);
          }

          // Prepare confitions.
          $condition_array = [
            'name' => $option['value'],
            'vid' => $vid,
          ];
          if ($is_translatable && $language !== LanguageInterface::LANGCODE_NOT_SPECIFIED) {
            $condition_array['langcode'] = $language;
          }

          $terms = \Drupal::entityTypeManager()
            ->getStorage('taxonomy_term')
            ->loadByProperties($condition_array);
          /** @var \Drupal\taxonomy\Entity\Term $term */
          $term = array_shift($terms);
          if (empty($term)) {
            $term = Term::create([
              'vid' => $vid,
              'name' => $option['value'],
              'langcode' => $language,
            ]);
            $term->save();
          }
          if ($is_translatable && $entity->hasTranslation($language)) {
            $entity->getTranslation($language)
              ->set($local_field_name, $term->id());
          }
          else {
            $entity->set($local_field_name, $term->id());
          }
        }
        else {
          if ($is_translatable) {
            $entity->getTranslation($language)->{$local_field_name}->value = $option['value'];
          }
          else {
            $entity->{$local_field_name}->value = $option['value'];
          }
        }
      }
      else {
        // Dealing with predefined options.
        if ($field_info->getType() === 'entity_reference') {
          if (!empty($field_info->getSetting('handler_settings')['auto_create_bundle'])) {
            $vid = $field_info->getSetting('handler_settings')['auto_create_bundle'];
          }
          else {
            $handler_settings = $field_info->getSetting('handler_settings');
            $handler_settings = reset($handler_settings);
            $vid = array_shift($handler_settings);
          }

          $terms = \Drupal::entityTypeManager()
            ->getStorage('taxonomy_term')
            ->loadByProperties([
              'gathercontent_option_ids' => $option['name'],
              'vid' => $vid,
            ]);

          /** @var \Drupal\taxonomy\Entity\Term $term */
          $term = array_shift($terms);
          if (!empty($term)) {
            if ($is_translatable) {
              $entity->getTranslation($language)
                ->set($local_field_name, $term->id());
            }
            else {
              $entity->set($local_field_name, $term->id());
            }
          }
        }
        else {
          if ($is_translatable) {
            $entity->getTranslation($language)->{$local_field_name}->value = $option['name'];
          }
          else {
            $entity->{$local_field_name}->value = $option['name'];
          }
        }
      }
    }
  }

  /**
   * Processing function for file type of field.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Object of node.
   * @param \Drupal\field\Entity\FieldConfig $field_info
   *   Local field Info object.
   * @param string $gc_field_name
   *   Name of field in GatherContent.
   * @param bool $is_translatable
   *   Indicator if node is translatable.
   * @param string $language
   *   Language of translation if applicable.
   * @param array $files
   *   Array of remote files.
   */
  protected function processFilesField(EntityInterface &$entity, FieldConfig $field_info, $gc_field_name, $is_translatable, $language, array $files) {
    $found_files = [];
    $local_field_name = $field_info->getName();
    /** @var \Drupal\field\Entity\FieldConfig $translatable_file_config */
    $translatable_file_config = $entity->getFieldDefinition($local_field_name);
    $third_party_settings = $translatable_file_config->get('third_party_settings');

    if (isset($third_party_settings['content_translation'])) {
      $translatable_file = $third_party_settings['content_translation']['translation_sync']['file'];
    }
    else {
      $translatable_file = NULL;
    }

    foreach ($files as $key => $file) {
      if ($file->field === $gc_field_name) {
        $drupal_files = \Drupal::entityQuery('file')
          ->condition('gc_id', $file->id)
          ->condition('filename', $file->fileName)
          ->execute();

        if (!empty($drupal_files)) {
          $drupal_file = reset($drupal_files);
          $found_files[] = ['target_id' => $drupal_file];
          unset($files[$key]);
        }
      }
      else {
        unset($files[$key]);
      }
    }

    if (!($entity->language()->getId() !== $language && $translatable_file === '0') && !empty($files)) {
      $file_dir = $translatable_file_config->getSetting('file_directory');
      $file_dir = PlainTextOutput::renderFromHtml(\Drupal::token()->replace($file_dir, []));

      $uri_scheme = $translatable_file_config->getFieldStorageDefinition()->getSetting('uri_scheme') . '://';

      $create_dir = \Drupal::service('file_system')->realpath($uri_scheme) . '/' . $file_dir;
      file_prepare_directory($create_dir, FILE_CREATE_DIRECTORY);

      $imported_files = $this->client->downloadFiles($files, $uri_scheme . $file_dir, $language);

      if (!empty($imported_files)) {
        foreach ($imported_files as $file) {
          $found_files[] = ['target_id' => $file];
        }

        if ($is_translatable) {
          $entity->getTranslation($language)->set($local_field_name, end($found_files));
        }
        else {
          $entity->set($local_field_name, end($found_files));
        }
      }
    }
  }

}
