<?php

namespace Drupal\gathercontent_ui\Form;

use Cheppers\GatherContent\GatherContentClientInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\gathercontent\Entity\Mapping;
use Drupal\node\Entity\Node;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a node deletion confirmation form.
 */
class ContentConfirmForm extends ConfirmFormBase {

  /**
   * Array of Node IDs.
   *
   * @var integer[]
   */
  protected $nodeIds;

  /**
   * The tempstore factory.
   *
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $tempStore;

  /**
   * The node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $manager;

  /**
   * Client for querying the GatherContent API.
   *
   * @var \Drupal\gathercontent\DrupalGatherContentClient
   */
  protected $client;

  /**
   * Constructs a DeleteMultiple form object.
   *
   * @param \Drupal\user\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\Core\Entity\EntityManagerInterface $manager
   *   The entity manager.
   */
  public function __construct(
    PrivateTempStoreFactory $temp_store_factory,
    EntityManagerInterface $manager,
    GatherContentClientInterface $client
  ) {
    $this->tempStore = $temp_store_factory->get('gathercontent_multistep_data');
    $this->storage = $manager->getStorage('node');
    $this->nodeIds = $this->tempStore->get('nodes');
    $this->client = $client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('user.private_tempstore'),
      $container->get('entity.manager'),
      $container->get('gathercontent.client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'content_confirm_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->formatPlural(count($this->nodeIds), 'Confirm selection (@count item)', 'Confirm selection (@count items)');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('admin.content');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Please review your selection.');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelText() {
    return $this->t('Back');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Continue');
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

    $nodes = Node::loadMultiple(isset($this->nodeIds) ? $this->nodeIds : []);
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

    $base_url = 'http://' . \Drupal::config('gathercontent.settings')
      ->get('gathercontent_urlkey') . '.gathercontent.com/item/';

    $content_table = [];
    foreach ($nodes as $item) {
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
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $content_table,
      '#empty' => t('No content available.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('confirm') && !empty($this->nodeIds)) {
      // Do something.
    }
  }

}
