<?php

namespace Drupal\gathercontent_upload_ui\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\gathercontent_ui\Form\ContentSelectForm;

/**
 * Class ContentUpdateSelectForm.
 *
 * @package Drupal\gathercontent\Form
 */
class ContentUploadSelectForm extends ContentSelectForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gathercontent_content_upload_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->store->set('nodes', array_filter($form_state->getValue('nodes')));
    $form_state->setRedirect('gathercontent_upload_ui.upload_confirm_form');
  }

}
