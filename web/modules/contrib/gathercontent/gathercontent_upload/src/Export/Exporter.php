<?php

namespace Drupal\gathercontent_upload\Export;

use Cheppers\GatherContent\DataTypes\Element;
use Cheppers\GatherContent\DataTypes\Item;
use Cheppers\GatherContent\DataTypes\Tab;
use Cheppers\GatherContent\GatherContentClientInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\gathercontent\Entity\Mapping;
use Drupal\gathercontent\MetatagQuery;
use Drupal\gathercontent_upload\Event\GatherUploadContentEvents;
use Drupal\gathercontent_upload\Event\PostNodeUploadEvent;
use Drupal\gathercontent_upload\Event\PreNodeUploadEvent;
use Drupal\node\NodeInterface;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class for handling import/update logic from GatherContent to Drupal.
 */
class Exporter implements ContainerInjectionInterface {

  /**
   * Drupal GatherContent Client.
   *
   * @var \Drupal\gathercontent\DrupalGatherContentClient
   */
  protected $client;

  protected $metatag;

  /**
   * DI GatherContent Client.
   */
  public function __construct(
    GatherContentClientInterface $client,
    MetatagQuery $metatag
  ) {
    $this->client = $client;
    $this->metatag = $metatag;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('gathercontent.client'),
      $container->get('gathercontent.metatag')
    );
  }

  /**
   * Getter GatherContentClient.
   */
  public function getClient() {
    return $this->client;
  }

  /**
   * Don't forget to add a finished callback and the operations array.
   */
  public static function getBasicExportBatch() {
    return [
      'title' => t('Uploading content ...'),
      'init_message' => t('Upload is starting ...'),
      'error_message' => t('An error occurred during processing'),
      'progress_message' => t('Processed @current out of @total.'),
      'progressive' => TRUE,
    ];
  }

  /**
   * Exports the changes made in Drupal contents.
   *
   * @param \Cheppers\GatherContent\DataTypes\Item $gc_item
   *   Item object.
   * @param \Drupal\node\NodeInterface $entity
   *   Node entity object.
   *
   * @return int|null|string
   *   Returns entity ID.
   *
   * @throws \Exception
   */
  public function export(Item $gc_item, NodeInterface $entity) {
    $gc_item = $this->processPanes($gc_item, $entity);

    $event = \Drupal::service('event_dispatcher')
      ->dispatch(GatherUploadContentEvents::PRE_NODE_UPLOAD, new PreNodeUploadEvent($entity, $gc_item->config));

    /** @var \Drupal\gathercontent_upload\Event\PreNodeUploadEvent $event */
    $config = $event->getGathercontentValues();
    $this->client->itemSavePost($gc_item->id, $config);

    \Drupal::service('event_dispatcher')
      ->dispatch(GatherUploadContentEvents::POST_NODE_UPLOAD, new PostNodeUploadEvent($entity, $config));

    return $entity->id();
  }

  /**
   * Return the mapping associated with the given Item.
   */
  public function getMapping(Item $gc_item) {
    $mapping_id = \Drupal::entityQuery('gathercontent_mapping')
      ->condition('gathercontent_project_id', $gc_item->projectId)
      ->condition('gathercontent_template_id', $gc_item->templateId)
      ->execute();

    if (empty($mapping_id)) {
      throw new Exception("Operation failed: Template not mapped.");
    }

    $mapping_id = reset($mapping_id);
    $mapping = Mapping::load($mapping_id);

    if ($mapping === NULL) {
      throw new Exception("No mapping found with id: $mapping_id");
    }

    return $mapping;
  }

  /**
   * Manages the panes and changes the Item object values.
   *
   * @param \Cheppers\GatherContent\DataTypes\Item $gc_item
   *   Item object.
   * @param \Drupal\node\NodeInterface $entity
   *   Node entity object.
   *
   * @return \Cheppers\GatherContent\DataTypes\Item
   *   Returns Item object.
   *
   * @throws \Exception
   */
  public function processPanes(Item $gc_item, NodeInterface $entity) {
    $mapping = $this->getMapping($gc_item);
    $mapping_data = unserialize($mapping->getData());

    if (empty($mapping_data)) {
      throw new Exception("Mapping data is empty.");
    }

    foreach ($gc_item->config as &$pane) {
      $is_translatable = \Drupal::moduleHandler()->moduleExists('content_translation')
        && \Drupal::service('content_translation.manager')
          ->isEnabled('node', $mapping->getContentType())
        && isset($mapping_data[$pane->id]['language'])
        && ($mapping_data[$pane->id]['language'] != Language::LANGCODE_NOT_SPECIFIED);
      if ($is_translatable) {
        $language = $mapping_data[$pane->id]['language'];
      }
      else {
        $language = Language::LANGCODE_NOT_SPECIFIED;
      }

      $pane = $this->processFields($pane, $entity, $mapping_data, $is_translatable, $language);
    }

    return $gc_item;
  }

  /**
   * Processes field data.
   *
   * @param \Cheppers\GatherContent\DataTypes\Tab $pane
   *   Pane object.
   * @param \Drupal\node\NodeInterface $entity
   *   Entity.
   * @param array $mapping_data
   *   Mapping array.
   * @param bool $is_translatable
   *   Translatable.
   * @param string $language
   *   Language.
   *
   * @return mixed
   *   Returns pane.
   */
  public function processFields(Tab $pane, NodeInterface $entity, array $mapping_data, $is_translatable, $language) {
    $exported_fields = [];
    foreach ($pane->elements as &$field) {
      if (isset($mapping_data[$pane->id]['elements'][$field->id])
        && !empty($mapping_data[$pane->id]['elements'][$field->id])
      ) {
        $local_field_id = $mapping_data[$pane->id]['elements'][$field->id];
        if ((isset($mapping_data[$pane->id]['type']) && $mapping_data[$pane->id]['type'] === 'content') || !isset($mapping_data[$pane->id]['type'])) {
          $local_id_array = explode('||', $local_field_id);
          $field_info = FieldConfig::load($local_id_array[0]);

          $current_entity = $entity;

          $type = '';
          $bundle = '';
          if ($local_id_array[0] === 'title') {
            $current_field_name = $local_id_array[0];
          }
          else {
            $current_field_name = $field_info->getName();
            $type = $field_info->getType();
            $bundle = $field_info->getTargetBundle();
          }

          $this->processTargets($current_entity, $current_field_name, $type, $bundle, $exported_fields, $local_id_array, $is_translatable, $language);

          $field = $this->processSetFields($field, $current_entity, $is_translatable, $language, $current_field_name, $type, $bundle);
        }
        elseif ($mapping_data[$pane->id]['type'] === 'metatag') {
          if (\Drupal::moduleHandler()->moduleExists('metatag') && $this->metatag->checkMetatag($entity->getType())) {
            $field = $this->processMetaTagFields($field, $entity, $local_field_id, $is_translatable, $language);
          }
        }
      }
    }

    return $pane;
  }

  /**
   * Processes the target ids for a field.
   *
   * @param \Drupal\Core\Entity\EntityInterface $current_entity
   *   Entity object.
   * @param string $current_field_name
   *   Current field name.
   * @param string $type
   *   Current type name.
   * @param string $bundle
   *   Current bundle name.
   * @param array $exported_fields
   *   Array of exported fields, preventing duplications.
   * @param array $local_id_array
   *   Array of mapped embedded field id array.
   * @param bool $is_translatable
   *   Translatable.
   * @param string $language
   *   Language.
   */
  public function processTargets(EntityInterface &$current_entity, &$current_field_name, &$type, &$bundle, array &$exported_fields, array $local_id_array, $is_translatable, $language) {
    $id_count = count($local_id_array);
    $entityTypeManager = \Drupal::entityTypeManager();

    for ($i = 0; $i < $id_count - 1; $i++) {
      $local_id = $local_id_array[$i];
      $field_info = FieldConfig::load($local_id);
      $current_field_name = $field_info->getName();
      $type = $field_info->getType();
      $bundle = $field_info->getTargetBundle();

      if ($is_translatable) {
        $target_field_value = $current_entity->getTranslation($language)->get($current_field_name)->getValue();
      }
      else {
        $target_field_value = $current_entity->get($current_field_name)->getValue();
      }

      if (!empty($target_field_value)) {
        $field_target_info = FieldConfig::load($local_id_array[$i + 1]);
        $entityStorage = $entityTypeManager
          ->getStorage($field_target_info->getTargetEntityTypeId());
        $child_field_name = $field_target_info->getName();
        $child_type = $field_info->getType();
        $child_bundle = $field_info->getTargetBundle();

        foreach ($target_field_value as $target) {
          $export_key = $target['target_id'] . '_' . $child_field_name;

          if (!empty($exported_fields[$export_key])) {
            continue;
          }

          $child_entity = $entityStorage->loadByProperties([
            'id' => $target['target_id'],
            'type' => $field_target_info->getTargetBundle(),
          ]);

          if (!empty($child_entity[$target['target_id']])) {
            $current_entity = $child_entity[$target['target_id']];
            $current_field_name = $child_field_name;
            $type = $child_type;
            $bundle = $child_bundle;

            if ($i == ($id_count - 2)) {
              $exported_fields[$export_key] = TRUE;
            }
            break;
          }
        }
      }
    }
  }

  /**
   * Processes meta fields.
   *
   * @param \Cheppers\GatherContent\DataTypes\Element $field
   *   Field object.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity object.
   * @param string $local_field_name
   *   Field name.
   * @param bool $is_translatable
   *   Translatable bool.
   * @param string $language
   *   Language string.
   *
   * @return \Cheppers\GatherContent\DataTypes\Element
   *   Returns field.
   */
  public function processMetaTagFields(Element $field, EntityInterface $entity, $local_field_name, $is_translatable, $language) {
    $metatag_fields = $this->metatag->getMetatagFields($entity->getType());

    foreach ($metatag_fields as $metatag_field) {
      if ($is_translatable) {
        $current_value = unserialize($entity->getTranslation($language)->{$metatag_field}->value);
      }
      else {
        $current_value = unserialize($entity->{$metatag_field}->value);
      }

      $field->value = $current_value[$local_field_name];
    }

    return $field;
  }

  /**
   * Set value of the field.
   *
   * @param \Cheppers\GatherContent\DataTypes\Element $field
   *   Field object.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity object.
   * @param bool $is_translatable
   *   Translatable bool.
   * @param string $language
   *   Language string.
   * @param string $local_field_name
   *   Field Name.
   * @param string $type
   *   Local field Info type string.
   * @param string $bundle
   *   Local field Info bundle string.
   *
   * @return \Cheppers\GatherContent\DataTypes\Element
   *   Returns field.
   */
  public function processSetFields(Element $field, EntityInterface $entity, $is_translatable, $language, $local_field_name, $type, $bundle) {
    switch ($field->type) {
      case 'files':
        // There is currently no API for manipulating with files.
        break;

      case 'choice_radio':
        /** @var \Cheppers\GatherContent\DataTypes\ElementRadio $field */

        $option_names = [];

        foreach ($field->options as &$option) {
          // Set selected to false for each option.
          $option['selected'] = FALSE;
          $option_names[] = $option['name'];
        }

        $selected = NULL;

        // Fetch local selected option.
        if ($type === 'entity_reference') {
          if ($is_translatable) {
            $targets = $entity->getTranslation($language)->{$local_field_name}->getValue();
          }
          else {
            $targets = $entity->{$local_field_name}->getValue();
          }

          $target = array_shift($targets);

          $condition_array = [
            'tid' => $target['target_id'],
          ];

          if (
            $is_translatable &&
            \Drupal::service('content_translation.manager')
              ->isEnabled('taxonomy_term', $bundle) &&
            $language !== LanguageInterface::LANGCODE_NOT_SPECIFIED
          ) {
            $condition_array['langcode'] = $language;
          }

          $terms = \Drupal::entityTypeManager()
            ->getStorage('taxonomy_term')
            ->loadByProperties($condition_array);

          /** @var \Drupal\taxonomy\Entity\Term $term */
          $term = array_shift($terms);
          if (!empty($term)) {
            $option_ids = $term->gathercontent_option_ids->getValue();
            $option_id = array_pop($option_ids);

            $selected = $option_id['value'];
          }
        }
        else {
          if ($is_translatable) {
            $selected = $entity->getTranslation($language)->{$local_field_name}->value;
          }
          else {
            $selected = $entity->{$local_field_name}->value;
          }
        }

        if (!in_array($selected, $option_names)) {
          // If it's other, then find that option in remote.
          foreach ($field->options as &$option) {
            if (isset($option['value'])) {
              $option['selected'] = TRUE;
              $option['value'] = $selected;
            }
          }
        }
        else {
          // If it's checkbox, find it by remote option name,
          // which should be same.
          foreach ($field->options as &$option) {
            if ($option['name'] == $selected) {
              $option['selected'] = TRUE;
            }
          }
        }
        break;

      case 'choice_checkbox':
        /** @var \Cheppers\GatherContent\DataTypes\ElementCheckbox $field */

        foreach ($field->options as &$option) {
          // Set selected to false for each option.
          $option['selected'] = FALSE;
        }

        $selected = [];

        // Fetch local selected option.
        if ($type === 'entity_reference') {
          if ($is_translatable) {
            $targets = $entity->getTranslation($language)->{$local_field_name}->getValue();
          }
          else {
            $targets = $entity->{$local_field_name}->getValue();
          }

          foreach ($targets as $target) {
            $condition_array = [
              'tid' => $target['target_id'],
            ];

            if (
              $is_translatable &&
              \Drupal::service('content_translation.manager')
                ->isEnabled('taxonomy_term', $bundle) &&
              $language !== LanguageInterface::LANGCODE_NOT_SPECIFIED
            ) {
              $condition_array['langcode'] = $language;
            }

            $terms = \Drupal::entityTypeManager()
              ->getStorage('taxonomy_term')
              ->loadByProperties($condition_array);

            /** @var \Drupal\taxonomy\Entity\Term $term */
            $term = array_shift($terms);
            if (!empty($term)) {
              $option_ids = $term->gathercontent_option_ids->getValue();
              $option_id = array_pop($option_ids);

              $selected[$option_id['value']] = TRUE;
            }
          }
        }
        else {
          if ($is_translatable) {
            $selected = $entity->getTranslation($language)->{$local_field_name}->value;
          }
          else {
            $selected = $entity->{$local_field_name}->value;
          }
        }

        // If it's checkbox, find it by remote option name,
        // which should be same.
        foreach ($field->options as &$option) {
          if (isset($selected[$option['name']])) {
            $option['selected'] = TRUE;
          }
        }
        break;

      case 'section':
        // We don't upload this because this field shouldn't be
        // edited.
        break;

      default:
        if ($local_field_name === 'title') {
          if ($is_translatable) {
            $field->value = $entity->getTranslation($language)
              ->getTitle();
          }
          else {
            $field->value = $entity->getTitle();
          }
        }
        else {
          if ($is_translatable) {
            $field->value = $entity->getTranslation($language)->{$local_field_name}->value;
          }
          else {
            $field->value = $entity->{$local_field_name}->value;
          }
        }
        break;
    }

    return $field;
  }

}
