<?php

namespace Drupal\gathercontent_ui\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class RemoveGatherContentLocalData.
 *
 * @package Drupal\gathercontent\Form
 */
class RemoveGatherContentLocalData extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gathercontent_ui_remove_local_data_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    drupal_set_message($this->t('This operation is irreversible and should be done before module uninstall!'), 'warning');

    $form['remove_message'] = [
      '#type' => 'markup',
      '#markup' => $this->t('This form removes the reference Gather Content IDs from your local Drupal site. The nodes keeps on your site.'),
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['delete'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete data'),
      '#button_type' => 'primary',
      '#return_value' => 'submit',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $batch = [
      'title' => $this->t('Deleting...'),
      'operations' => [
        [static::class . '::deleteAllEntity', ['gathercontent_operation_item']],
        [static::class . '::deleteAllEntity', ['gathercontent_operation']],
        [static::class . '::cleanUpGcData', ['node']],
        [static::class . '::cleanUpGcData', ['file']],
      ],
    ];
    batch_set($batch);

    $form_state->setRedirect('system.modules_uninstall');
    drupal_set_message($this->t('Now you can try to uninstall the Gather Content module.'));
  }

  /**
   * Delete gathercontent_operation and gathercontent_operation_item entities.
   *
   * @param string $entity_type_id
   *   Entity type ID of which we want to delete entities.
   * @param array $context
   *   Array of context.
   */
  public static function deleteAllEntity($entity_type_id, array &$context) {
    $entity_type_manager = \Drupal::entityTypeManager();
    if (empty($context['sandbox'])) {
      $context['sandbox']['num_of_deleted_items'] = 0;
      $context['sandbox']['current_id'] = 0;
      $context['sandbox']['total_count'] = \Drupal::entityQuery($entity_type_id)->count()->execute();
    }
    $steps = 50;
    $entity_ids = \Drupal::entityQuery($entity_type_id)
      ->condition($entity_type_manager->getDefinition($entity_type_id)->getKey('id'), $context['sandbox']['current_id'], '>')
      ->sort($entity_type_manager->getDefinition($entity_type_id)->getKey('id'))
      ->range(0, $steps)
      ->execute();
    foreach ($entity_ids as $entity_id) {
      $context['sandbox']['num_of_deleted_items']++;
      $context['sandbox']['current_id'] = $entity_id;
      $entity_type_manager->getStorage($entity_type_id)->load($entity_id)->delete();
    }
    if ($context['sandbox']['num_of_deleted_items'] != $context['sandbox']['total_count']) {
      $context['finished'] = $context['sandbox']['num_of_deleted_items'] / $context['sandbox']['total_count'];
    }
  }

  /**
   * Clean GC IDs from node and file entity.
   *
   * @param string $entity_type_id
   *   Entity type ID of which we want to clean entities.
   * @param array $context
   *   Array of context.
   */
  public static function cleanUpGcData($entity_type_id, array &$context) {
    if (empty($context['sandbox'])) {
      $context['sandbox']['num_of_precessed_items'] = 0;
      $context['sandbox']['current_id'] = 0;
      $context['sandbox']['total_count'] = \Drupal::entityQuery($entity_type_id)
        ->condition('gc_id', NULL, 'IS NOT NULL')
        ->count()
        ->execute();
    }

    $limit = 50;
    $id_key = \Drupal::entityTypeManager()->getDefinition($entity_type_id)->getKey('id');
    $entity_ids = \Drupal::entityQuery($entity_type_id)
      ->condition($id_key, $context['sandbox']['current_id'], '>')
      ->condition('gc_id', NULL, 'IS NOT NULL')
      ->sort($id_key)
      ->range(0, $limit)
      ->execute();

    foreach ($entity_ids as $entity_id) {
      $context['sandbox']['num_of_precessed_items']++;
      $context['sandbox']['current_id'] = $entity_id;
      /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
      $entity = \Drupal::entityTypeManager()->getStorage($entity_type_id)->load($entity_id);
      $entity->set('gc_id', NULL);
      if ($entity->hasField('gc_mapping_id')) {
        $entity->set('gc_mapping_id', NULL);
      }
      $entity->save();
    }

    if ($context['sandbox']['num_of_precessed_items'] != $context['sandbox']['total_count']) {
      $context['finished'] = $context['sandbox']['num_of_precessed_items'] / $context['sandbox']['total_count'];
    }
  }

}
