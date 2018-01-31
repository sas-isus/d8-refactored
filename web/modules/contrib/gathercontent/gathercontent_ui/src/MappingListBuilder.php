<?php

namespace Drupal\gathercontent_ui;

use Cheppers\GatherContent\GatherContentClientInterface;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\gathercontent\DrupalGatherContentClient;

/**
 * Provides a listing of GatherContent Mapping entities.
 */
class MappingListBuilder extends ConfigEntityListBuilder {

  protected $templates;

  /**
   * Client for querying the GatherContent API.
   *
   * @var \Drupal\gathercontent\DrupalGatherContentClient
   */
  protected $client;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeInterface $entity_type,
    EntityStorageInterface $storage,
    GatherContentClientInterface $client
  ) {
    parent::__construct($entity_type, $storage);
    $this->client = $client;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('gathercontent.client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface|\Drupal\Core\Entity\Query\QueryInterface $entity_query */
    $entity_query = \Drupal::service('entity.query')
      ->get('gathercontent_mapping');
    $query_string = \Drupal::request()->query;
    $headers = $this->buildHeader();

    $entity_query->pager(100);
    if ($query_string->has('order')) {
      foreach ($headers as $header) {
        if (is_array($header) && $header['data'] === $query_string->get('order')) {
          $sort = 'ASC';
          if ($query_string->has('sort') && $query_string->get('sort') === 'asc' || $query_string->get('sort') === 'desc') {
            $sort = Unicode::strtoupper($query_string->get('sort'));
          }
          $entity_query->sort($header['field'], $sort);
        }
      }
    }
    $entity_query->tableSort($headers);
    $entity_ids = $entity_query->execute();

    return $this->storage->loadMultiple($entity_ids);
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    return [
      'gathercontent_project' => [
        'data' => $this->t('GatherContent Project'),
        'field' => 'gathercontent_project',
        'specifier' => 'gathercontent_project',
      ],
      'gathercontent_template' => [
        'data' => $this->t('GatherContent Template'),
        'field' => 'gathercontent_template',
        'specifier' => 'gathercontent_template',
      ],
      'content_type_name' => [
        'data' => $this->t('Content type'),
        'field' => 'content_type_name',
        'specifier' => 'content_type_name',
      ],
      'updated_gathercontent' => [
        'data' => $this->t('Last updated in GatherContent'),
      ],
      'updated_drupal' => [
        'data' => $this->t('Updated in Drupal'),
        'field' => 'updated_drupal',
        'specifier' => 'updated_drupal',
      ],
    ] + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\gathercontent\Entity\Mapping $entity */
    $exists = isset($this->templates[$entity->getGathercontentTemplateId()]);
    $row['project'] = $entity->getGathercontentProject();
    $row['gathercontent_template'] = $entity->getGathercontentTemplate();
    $row['content_type'] = $entity->getFormattedContentType();
    $row['updated_gathercontent'] = ($exists ? \Drupal::service('date.formatter')
      ->format($this->templates[$entity->getGathercontentTemplateId()], 'custom', 'M d, Y - H:i') : t("Deleted"));
    $row['updated_drupal'] = $entity->getFormatterUpdatedDrupal();
    if ($exists) {
      $row = $row + parent::buildRow($entity);
    }
    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $account_id = DrupalGatherContentClient::getAccountId();
    if (!$account_id) {
      return parent::render();
    }

    $projects = $this->client->getActiveProjects($account_id);

    foreach ($projects as $project) {
      $remote_templates = $this->client->templatesGet($project->id);
      foreach ($remote_templates as $remote_template) {
        $this->templates[$remote_template->id] = $remote_template->updatedAt;
      }
    }

    return parent::render();
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = [];
    if ($entity->access('update') && $entity->hasLinkTemplate('edit-form')) {
      $operations['edit'] = [
        'title' => $entity->hasMapping() ? $this->t('Edit') : $this->t('Create'),
        'weight' => 10,
        'url' => $entity->urlInfo('edit-form'),
      ];
    }
    if ($entity->access('delete') && $entity->hasLinkTemplate('delete-form')) {
      $operations['delete'] = [
        'title' => $this->t('Delete'),
        'weight' => 100,
        'url' => $entity->urlInfo('delete-form'),
      ];
    }
    return $operations;
  }

}
