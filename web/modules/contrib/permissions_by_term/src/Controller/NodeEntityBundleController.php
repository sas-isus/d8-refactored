<?php

namespace Drupal\permissions_by_term\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityFieldManager;
use Drupal\permissions_by_term\Service\NodeEntityBundleInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class NodeEntityBundleController
 *
 * @package Drupal\permissions_by_term\Controller
 */
class NodeEntityBundleController extends ControllerBase {

  /**
   * @var EntityFieldManager
   */
  private $entityFieldManager;

  /**
   * @var NodeEntityBundleInfo
   */
  private $nodeEntityBundleInfo;

  public function __construct(EntityFieldManager $entityFieldManager, NodeEntityBundleInfo $nodeEntityBundleInfo) {
    $this->entityFieldManager = $entityFieldManager;
    $this->nodeEntityBundleInfo = $nodeEntityBundleInfo;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_field.manager'),
      $container->get('permissions_by_term.node_entity_bundle_info')
    );
  }

  /**
   * @param string $nodeType
   *
   * @return JsonResponse
   */
  public function getFormInfoByContentType($nodeType) {
    $fields = $this->entityFieldManager->getFieldDefinitions('node', $nodeType);

    $fieldNames = [];
    foreach ($fields as $field) {
      $fieldDefinitionSettings = $field->getSettings();
      if (!empty($fieldDefinitionSettings['target_type']) && $fieldDefinitionSettings['target_type'] == 'taxonomy_term') {
        $fieldNames[] = $field->getFieldStorageDefinition()->getName();
      }
    }

    return new JsonResponse(
      [
        'taxonomyRelationFieldNames' => $fieldNames,
        'permissions'                => $this->nodeEntityBundleInfo->getPermissions()
      ]
    );
  }

  /**
   * @return JsonResponse
   */
  public function getFormInfoByUrl() {

    $contentType = $this->getContentType(\Drupal::request()->query->get('url'));

    $fields = $this->entityFieldManager->getFieldDefinitions('node', $contentType);

    $fieldNames = [];
    foreach ($fields as $field) {
      $fieldDefinitionSettings = $field->getSettings();
      if (!empty($fieldDefinitionSettings['target_type']) && $fieldDefinitionSettings['target_type'] == 'taxonomy_term') {
        $fieldNames[] = $field->getFieldStorageDefinition()->getName();
      }
    }

    return new JsonResponse(
      [
        'taxonomyRelationFieldNames' => $fieldNames,
        'permissions'                => $this->nodeEntityBundleInfo->getPermissions()
      ]
    );
  }

  private function getContentType($nodeEditPath) {
    preg_match_all('!\d+!', $nodeEditPath, $matches);
    $nid = $matches[0][0];

    $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);

    return $node->getType();
  }

}
