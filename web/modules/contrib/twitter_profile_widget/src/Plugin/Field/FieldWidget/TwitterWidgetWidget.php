<?php

namespace Drupal\twitter_profile_widget\Plugin\Field\FieldWidget;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'twitter_widget' widget.
 *
 * @FieldWidget(
 *   id = "twitter_widget",
 *   label = @Translation("Twitter widget"),
 *   field_types = {
 *     "twitter_widget"
 *   }
 * )
 */
class TwitterWidgetWidget extends WidgetBase {

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Configuration Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $config;

  /**
   * Constructs a WidgetBase object.
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config_factory service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, MessengerInterface $messenger, ConfigFactory $config_factory) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->messenger = $messenger;
    $this->config = $config_factory->get('twitter_profile_widget.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('messenger'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    if (!$this->config->get('twitter_widget_key')) {
      $this->messenger->addWarning($this->t('Credentials for the Twitter API have not been configured or are invalid. Review the <a href=":widget">Twitter widget</a> settings.', [':widget' => '/admin/config/media/twitter_profile_widget']));
    }
    $field_name = $items->getName();
    // Handle scenarios of nested forms (i.e., Layout Builder).
    if (!empty($element['#field_parents'])) {
      $original_field_name = '[' . $field_name . ']';
      foreach ($element['#field_parents'] as $i => $parent) {
        if ($i === 0) {
          $field_name = $parent;
        }
        else {
          $field_name .= '[' . $parent . ']';
        }
      }
      $field_name .= $original_field_name;
    }
    $item = $items[$delta];
    $element['headline'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Headline'),
      '#description' => $this->t('Optional header that appears above the tweets.'),
      '#default_value' => isset($item->headline) ? $item->headline : 'Latest Tweets',
    ];
    $options = [
      'status' => 'User tweets',
      'timeline' => 'User timeline',
      'favorites' => 'Favorited by user',
      'search' => 'Search (Twitter-wide)',
    ];
    $element['list_type'] = [
      '#type' => 'select',
      '#title' => $this->t('List type'),
      '#options' => $options,
      '#default_value' => isset($item->list_type) ? $item->list_type : 'status',
    ];
    $element['account'] = [
      '#type' => 'textfield',
      '#title' => $this->t('User account'),
      '#description' => $this->t('The username (handle) from which to pull tweets.'),
      '#default_value' => isset($item->account) ? $item->account : '',
      '#states' => [
        'invisible' => [
          ':input[name="' . $field_name . '[0][list_type]"]' => ['value' => 'search'],
        ],
      ],
    ];
    $element['search'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search'),
      '#description' => $this->t('A search query, which may include Twitter <a href=":examples" target="blank">query operators</a>. Results are sorted based on Twitter ranking algorithm.', [':examples' => 'https://dev.twitter.com/rest/public/search#query-operators']),
      '#default_value' => isset($item->search) ? $item->search : '',
      '#states' => [
        'visible' => [
          ':input[name="' . $field_name . '[0][list_type]"]' => ['value' => 'search'],
        ],
      ],
    ];
    $element['timeline'] = [
      '#type' => 'textfield',
      '#title' => $this->t('User list'),
      '#description' => $this->t('Provide the human-readable name a list owned by the username above. Lists are found at https://twitter.com/[username]/lists . The timeline must already exist in Twitter to return any results.'),
      '#default_value' => isset($item->timeline) ? $item->timeline : '',
      '#states' => [
        'visible' => [
          ':input[name="' . $field_name . '[0][list_type]"]' => ['value' => 'timeline'],
        ],
      ],
    ];
    $element['count'] = [
      '#type' => 'select',
      '#title' => $this->t('Number of tweets to display'),
      '#options' => array_combine(range(1, 10), range(1, 10)),
      '#default_value' => isset($item->count) ? $item->count : 5,
    ];
    $element['retweets'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display retweets'),
      '#default_value' => isset($item->retweets) ? $item->retweets : 1,
      '#states' => [
        'visible' => [
          ':input[name="' . $field_name . '[0][list_type]"]' => [
            ['value' => 'status'],
            ['value' => 'timeline'],
          ],
        ],
      ],
    ];
    $element['replies'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display replies'),
      '#default_value' => isset($item->replies) ? $item->replies : 1,
      '#states' => [
        'visible' => [
          ':input[name="' . $field_name . '[0][list_type]"]' => [
            ['value' => 'status'],
            ['value' => 'timeline'],
          ],
        ],
      ],
    ];
    $element['view_all'] = [
      '#type' => 'textfield',
      '#title' => $this->t('"View all..." text'),
      '#description' => $this->t('Optional text displayed at the bottom of the widget, linking to Twitter.'),
      '#default_value' => isset($item->view_all) ? $item->view_all : 'View on Twitter',
    ];
    $element['#element_validate'] = [[$this, 'validate']];
    return $element;
  }

  /**
   * Validate the Twitter block parameters.
   */
  public function validate($element, FormStateInterface $form_state) {
    if (!$this->config->get('twitter_widget_key')) {
      $form_state->setError($element, $this->t('Credentials for the Twitter API have not been configured or are invalid. Review the <a href=":widget">Twitter widget</a> settings.', [':widget' => '/admin/config/media/twitter_profile_widget']));
    }
    $values = $form_state->getValues();
    // Handle parents from Layout Builder.
    $fields = isset($values['settings']['block_form']) ? $values['settings']['block_form']['field_twitter_profile_widget'][0] : $values['field_twitter_profile_widget'][0];
    if ($fields['list_type'] == 'search' && $fields['search'] == '') {
      $form_state->setError($element['search'], $this->t('The "Search term" type requires entering a search parameter.'));
    }
    if ($fields['list_type'] != 'search' && $fields['account'] == '') {
      $form_state->setError($element['account'], $this->t('This Twitter widget type requires that you enter an account handle.'));
    }
    if ($fields['list_type'] == 'timeline' && $fields['timeline'] == '') {
      $form_state->setError($element['timeline'], $this->t('The "User timeline" type requires entering a timeline list.'));
    }
  }

}
