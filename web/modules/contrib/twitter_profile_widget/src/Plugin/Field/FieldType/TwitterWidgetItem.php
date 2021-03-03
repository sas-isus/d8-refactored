<?php

namespace Drupal\twitter_profile_widget\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'twitter_widget' field type.
 *
 * @FieldType(
 *   id = "twitter_widget",
 *   label = @Translation("Twitter widget"),
 *   description = @Translation("Reference a twitter feed"),
 *   category = @Translation("Social Media"),
 *   default_widget = "twitter_widget",
 *   default_formatter = "twitter_widget"
 * )
 */
class TwitterWidgetItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['headline'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Headline'))
      ->setRequired(FALSE);
    $properties['list_type'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Type'))
      ->setRequired(TRUE);
    $properties['account'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Twitter Account'))
      ->setRequired(FALSE);
    $properties['search'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Search query'))
      ->setRequired(FALSE);
    $properties['timeline'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Timeline List'))
      ->setRequired(FALSE);
    $properties['count'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Number of tweets'))
      ->setRequired(TRUE);
    $properties['retweets'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Include retweets'))
      ->setRequired(FALSE);
    $properties['replies'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Include replies'))
      ->setRequired(FALSE);
    $properties['view_all'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('View all text'))
      ->setRequired(FALSE);
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'headline' => [
          'type' => 'varchar',
          'length' => 255,
          'binary' => FALSE,
        ],
        'list_type' => [
          'type' => 'varchar',
          'length' => 255,
          'binary' => FALSE,
        ],
        'account' => [
          'type' => 'varchar',
          'length' => 255,
          'binary' => FALSE,
        ],
        'search' => [
          'type' => 'varchar',
          'length' => 255,
          'binary' => FALSE,
        ],
        'timeline' => [
          'type' => 'varchar',
          'length' => 255,
          'binary' => FALSE,
        ],
        'count' => [
          'type' => 'int',
          'size' => 'tiny',
        ],
        'retweets' => [
          'type' => 'int',
          'size' => 'tiny',
        ],
        'replies' => [
          'type' => 'int',
          'size' => 'tiny',
        ],
        'view_all' => [
          'type' => 'varchar',
          'length' => 255,
          'binary' => FALSE,
        ],
      ],
    ];
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $values['headline'] = Random::word(3);
    $values['list_type'] = 'account';
    $values['account'] = 'markfullmer';
    $values['search'] = '';
    $values['timeline'] = '';
    $values['count'] = array_rand(range(1, 10), 1);
    $values['retweets'] = array_rand(range(0, 1), 1);
    $values['replies'] = array_rand(range(0, 1), 1);
    $values['view_all'] = Random::word(3);
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $account = $this->get('account')->getValue();
    $search = $this->get('search')->getValue();
    $timeline = $this->get('timeline')->getValue();
    return empty($account) && empty($search) && empty($timeline);
  }

}
