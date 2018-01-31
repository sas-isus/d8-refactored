<?php

namespace Drupal\gathercontent_test;

use Cheppers\GatherContent\DataTypes\ElementFiles;
use Cheppers\GatherContent\DataTypes\File;
use Cheppers\GatherContent\DataTypes\Item;
use Cheppers\GatherContent\DataTypes\Status;
use Cheppers\GatherContent\DataTypes\Tab;
use Drupal\Core\Language\LanguageInterface;
use Drupal\gathercontent\Entity\Mapping;
use Drupal\taxonomy\Entity\Term;

/**
 * A class for getting static test data.
 */
class MockData {

  const CHECKBOX_TAXONOMY_NAME = 'checkbox_test_taxonomy';
  const RADIO_TAXONOMY_NAME = 'radio_test_taxonomy';

  const METATAG_FIELD = 'field_metatag_test';

  const TRANSLATED_TAB = 'tab1503302417527';
  const METATAG_TAB = 'tab1503403907382';

  public static $drupalRoot = '';

  /**
   * Utility function.
   */
  public static function getUniqueInt() {
    static $counter = 1;
    return $counter++;
  }

  /**
   * Create the default test taxonomy terms.
   */
  public static function createTaxonomyTerms() {
    $terms = [];

    $terms[] = Term::create([
      'vid' => 'checkbox_test_taxonomy',
      'name' => 'First checkbox',
      'gathercontent_option_ids' => ['op1502871154842', 'op15028711548427481'],
    ]);

    $terms[] = Term::create([
      'vid' => 'checkbox_test_taxonomy',
      'name' => 'Second checkbox',
      'gathercontent_option_ids' => ['op1502871154843', 'op15028711548435101'],
    ]);

    $terms[] = Term::create([
      'vid' => 'checkbox_test_taxonomy',
      'name' => 'Third checkbox',
      'gathercontent_option_ids' => ['op1502871154844', 'op15028711548447560'],
    ]);

    $terms[] = Term::create([
      'vid' => 'radio_test_taxonomy',
      'name' => 'First radio',
      'gathercontent_option_ids' => ['op1502871172350', 'op15028711723507803'],
    ]);

    $terms[] = Term::create([
      'vid' => 'radio_test_taxonomy',
      'name' => 'Second radio',
      'gathercontent_option_ids' => ['op1502871172351', 'op1502871172351707'],
    ]);

    $terms[] = Term::create([
      'vid' => 'radio_test_taxonomy',
      'name' => 'Third radio',
      'gathercontent_option_ids' => ['op1502871172352', 'op15028711723524246'],
    ]);

    return $terms;
  }

  /**
   * Creates a GC Item corresponding to a mapping.
   */
  public static function createItem(Mapping $mapping, array $selectedCheckboxes, array $selectedRadioboxes) {
    $mappingData = unserialize($mapping->getData());
    $mainTabElements = reset($mappingData)['elements'];
    $mainTabId = key($mappingData);
    $template = unserialize($mapping->getTemplate())->data;
    $tabs = $template->config;

    $item = new Item();
    $item->id = static::getUniqueInt();
    $item->name = 'test item name ' . $item->id;
    $item->projectId = $template->project_id;
    $item->templateId = $template->id;

    foreach ($tabs as $i => $tab) {
      $newTab = new Tab(json_decode(json_encode($tab), TRUE));
      foreach ($newTab->elements as $element) {
        switch ($element->type) {
          case 'text':
            if ($element->plainText) {
              if ($newTab->id === MockData::METATAG_TAB) {
                // Metatag.
                $element->setValue($element->label . ' ' . static::getUniqueInt());
              }
              else {
                // Title.
                $element->setValue($item->name . ($newTab->id === MockData::TRANSLATED_TAB ? ' translated' : ''));
              }
            }
            else {
              // If translation.
              if ($newTab->id === MockData::TRANSLATED_TAB) {
                // Get the original element value and append 'translated' to it.
                $fieldId = $mappingData[$newTab->id]['elements'][$element->id];
                $mainElementId = array_search($fieldId, $mainTabElements);
                $element->setValue($item->config[$mainTabId]->elements[$mainElementId]->getValue() . ' translated');
              }
              else {
                $element->setValue('test text ' . static::getUniqueInt());
              }
            }
            break;

          case 'files':
            // Files are not stored here, only file field definitions.
            break;

          case 'section':
            // If translation.
            if ($newTab->id === MockData::TRANSLATED_TAB) {
              $fieldId = $mappingData[$newTab->id]['elements'][$element->id];
              $mainElementId = array_search($fieldId, $mainTabElements);
              $element->subtitle = $item->config[$mainTabId]->elements[$mainElementId]->subtitle . ' translated';
            }
            else {
              $element->subtitle = 'test section subtitle ' . static::getUniqueInt();
            }
            break;

          case 'choice_checkbox':
            foreach ($element->options as $i => $option) {
              $element->options[$i]['selected'] = $selectedCheckboxes[$i];
            }
            break;

          case 'choice_radio':
            foreach ($element->options as $i => $option) {
              $element->options[$i]['selected'] = $selectedRadioboxes[$i];
            }
            break;
        }
      }
      $item->config[$newTab->id] = $newTab;
    }

    return $item;
  }

  /**
   * Create a file for every file element in item.
   */
  public static function createFile(Item $item) {
    $fileElements = array_filter(reset($item->config)->elements, function ($element) {
      return $element instanceof ElementFiles;
    });
    $files = [];

    foreach ($fileElements as $element) {
      $file = new File();
      $file->id = static::getUniqueInt();
      $file->userId = static::getUniqueInt();
      $file->itemId = $item->id;
      $file->field = $element->id;
      $file->url = static::$drupalRoot . '/' . drupal_get_path('module', 'gathercontent_test') . '/images/test.png';
      $file->fileName = 'test.jpg';
      $file->size = 60892;
      $file->type = 'field';
      $file->createdAt = '2017-08-18 15:48:10';
      $file->updatedAt = '2017-08-18 15:48:10';
      $files[$file->id] = $file;
    }

    return $files;
  }

  /**
   * After installing the test configs read the mapping.
   */
  public static function getMapping() {
    $mapping_id = \Drupal::entityQuery('gathercontent_mapping')->execute();
    $mapping_id = reset($mapping_id);
    return Mapping::load($mapping_id);
  }

  /**
   * Get mock statuses.
   */
  public static function getStatuses() {
    $statuses = [];

    $status1 = new Status();
    $status1->id = 1;
    $status1->isDefault = TRUE;
    $status1->position = 1;
    $status1->color = '#FF0000';
    $status1->name = 'Status 1';
    $status1->canEdit = TRUE;
    $statuses[] = $status1;

    $status2 = new Status();
    $status2->id = 2;
    $status2->position = 2;
    $status2->color = '#00FF00';
    $status2->name = 'Status 2';
    $status2->canEdit = TRUE;
    $statuses[] = $status2;

    $status3 = new Status();
    $status3->id = 3;
    $status3->position = 3;
    $status3->color = '#0000FF';
    $status3->name = 'Status 3';
    $status3->canEdit = TRUE;
    $statuses[] = $status3;

    return $statuses;
  }

}
