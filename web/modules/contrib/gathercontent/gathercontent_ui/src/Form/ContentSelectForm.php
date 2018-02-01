<?php

namespace Drupal\gathercontent_ui\Form;

use Cheppers\GatherContent\GatherContentClientInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\gathercontent\Entity\Mapping;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ContentUpdateSelectForm.
 *
 * @package Drupal\gathercontent\Form
 */
class ContentSelectForm extends MultistepFormBase {

  /**
   * @var \Drupal\gathercontent\DrupalGatherContentClient
   */
  protected $client;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    DateFormatterInterface $date_formatter,
    PrivateTempStoreFactory $temp_store_factory,
    GatherContentClientInterface $client
  ) {
    parent::__construct($entity_type_manager, $date_formatter, $temp_store_factory);
    $this->client = $client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('date.formatter'),
      $container->get('user.private_tempstore'),
      $container->get('gathercontent.client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'content_select_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $created_mapping_ids = Mapping::loadMultiple();
    $projects = $contents = [];
    $mapping_array = [];
    foreach ($created_mapping_ids as $mapping) {
      /** @var \Drupal\gathercontent\Entity\Mapping $mapping */
      if ($mapping->hasMapping()) {
        $projects[$mapping->getGathercontentProjectId()] = $mapping->getGathercontentProject();
        $mapping_array[$mapping->id()] = [
          'gc_template' => $mapping->getGathercontentTemplate(),
          'ct' => $mapping->getContentTypeName(),
        ];
      }
    }

    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $node_ids = $query->condition('gc_id', NULL, 'IS NOT')
      ->condition('gc_mapping_id', NULL, 'IS NOT')
      ->execute();
    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($node_ids);
    $selected_projects = [];

    foreach ($created_mapping_ids as $mapping) {
      if (!in_array($mapping->getGathercontentProjectId(), $selected_projects)) {
        $selected_projects[] = $mapping->getGathercontentProjectId();
        /** @var \Cheppers\GatherContent\DataTypes\Item[] $content */
        $content = $this->client->itemsGet($mapping->getGathercontentProjectId());

        foreach ($content as $c) {
          $single_content = [];
          $single_content['gc_updated'] = $c->updatedAt;
          $single_content['status'] = $c->status;
          $single_content['name'] = $c->name;
          $single_content['project_id'] = $c->projectId;
          $contents[$c->id] = $single_content;
        }
      }
    }

    $form['filter'] = [
      '#type' => 'markup',
      '#markup' => '<div class="gc-table--filter-wrapper clearfix"></div>',
      '#weight' => 0,
    ];

    $form['counter'] = [
      '#type' => 'markup',
      '#markup' => '<div class="gc-table--counter"></div>',
      '#weight' => 1,
    ];

    $base_url = 'http://' . \Drupal::config('gathercontent.settings')
      ->get('gathercontent_urlkey') . '.gathercontent.com/item/';

    $content_table = [];
    foreach ($nodes as $item) {
      if (!isset($contents[$item->gc_id->value]['status']->name)) {
        // Don't show deleted items or items which belong to another account.
        continue;
      }

      /** @var \Drupal\node\Entity\Node $item */
      $content_table[$item->id()] = [
        'status' => [
          'data' => [
            'color' => [
              '#type' => 'html_tag',
              '#tag' => 'div',
              '#value' => ' ',
              '#attributes' => [
                'style' => 'width:20px; height: 20px; float: left; margin-right: 5px; background: ' . $contents[$item->gc_id->value]['status']->color,
              ],
            ],
            'label' => [
              '#plain_text' => $contents[$item->gc_id->value]['status']->name,
            ],
          ],
          'class' => ['gc-item', 'status-item'],
        ],
        'gathercontent_project' => [
          'data' => $projects[$contents[$item->gc_id->value]['project_id']],
        ],
        'title' => [
          'data' => $item->getTitle(),
          'class' => ['gc-item', 'gc-item--name'],
        ],
        'gathercontent_title' => [
          'data' => $contents[$item->gc_id->value]['name'],
        ],
        'gathercontent_updated' => [
          'data' => date('F d, Y - H:i', strtotime($contents[$item->gc_id->value]['gc_updated']->date)),
          'class' => ['gc-item', 'gc-item-date'],
          'data-date' => date('Y-m-d.H:i:s', strtotime($contents[$item->gc_id->value]['gc_updated']->date)),
        ],
        'drupal_updated' => [
          'data' => date('F d, Y - H:i', $item->getChangedTime()),
          'class' => ['gc-item', 'gc-item-date'],
          'data-date' => date('Y-m-d.H:i:s', $item->getChangedTime()),
        ],
        'content_type' => [
          'data' => $mapping_array[$item->gc_mapping_id->value]['ct'],
        ],
        'gathercontent_template' => [
          'data' => $mapping_array[$item->gc_mapping_id->value]['gc_template'],
          'class' => ['template-name-item'],
        ],
        'drupal_open' => [
          'data' => Link::fromTextAndUrl($this->t('Open'), Url::fromUri('entity:node/' . $item->id()))
            ->toRenderable(),
        ],
        'gathercontent_open' => [
          'data' => Link::fromTextAndUrl($this->t('Open'), Url::fromUri($base_url . $item->gc_id->value))
            ->toRenderable(),
        ],
      ];
    }

    $header = [
      'status' => $this->t('Status'),
      'gathercontent_project' => $this->t('GatherContent project'),
      'title' => $this->t('Item Name'),
      'gathercontent_title' => $this->t('GatherContent item name'),
      'drupal_updated' => $this->t('Last updated in Drupal'),
      'gathercontent_updated' => $this->t('Last updated in GatherContent'),
      'content_type' => $this->t('Content type name'),
      'gathercontent_template' => $this->t('GatherContent template'),
      'drupal_open' => $this->t('Open in Drupal'),
      'gathercontent_open' => $this->t('Open in GatherContent'),
    ];

    $form['nodes'] = [
      '#weight' => 2,
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $content_table,
      '#empty' => t('No content available.'),
      '#default_value' => $this->store->get('nodes') ? $this->store->get('nodes') : [],
      '#attributes' => [
        'class' => [
          'tablesorter-enabled',
        ],
      ],
      '#attached' => [
        'library' => [
          'gathercontent_ui/tablesorter-mottie',
        ],
        'drupalSettings' => [
          'gatherContent' => [
            'tableSorterOptionOverrides' => [
              'headers' => [
                '0' => [
                  'sorter' => FALSE,
                ],
                '9' => [
                  'sorter' => FALSE,
                ],
                '10' => [
                  'sorter' => FALSE,
                ],
              ],
            ],
          ],
        ],
      ],
    ];

    $form['actions']['submit']['#value'] = $this->t('Next');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Do something.
  }

}
