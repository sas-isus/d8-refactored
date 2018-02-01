<?php

namespace Drupal\gathercontent_upload_ui\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\gathercontent\Entity\Operation;
use Drupal\gathercontent_ui\Form\ContentConfirmForm;
use Drupal\node\Entity\Node;

/**
 * Provides a node deletion confirmation form.
 */
class ContentUploadConfirmForm extends ContentConfirmForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'node_update_from_gc_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->formatPlural(count($this->nodeIds), 'Confirm upload selection (@count item)', 'Confirm upload selection (@count items)');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('gathercontent_upload_ui.upload_select_form');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Please review your selection before uploading.');
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
    return $this->t('Upload');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('confirm') && !empty($this->nodeIds)) {
      $operation = Operation::create([
        'type' => 'upload',
      ]);
      $operation->save();

      $nodes = Node::loadMultiple($this->nodeIds);
      $operations = [];
      foreach ($nodes as $node) {
        $operations[] = [
          'gathercontent_upload_process',
          [
            $node,
            $operation->uuid(),
          ],
        ];
      }

      $batch = [
        'title' => t('Uploading content ...'),
        'operations' => $operations,
        'finished' => 'gathercontent_upload_finished',
        'init_message' => t('Upload is starting ...'),
        'progress_message' => t('Processed @current out of @total.'),
        'error_message' => t('An error occurred during processing'),
      ];

      $this->tempStore->delete('nodes');
      batch_set($batch);
    }
  }

}
