<?php

namespace Drupal\gathercontent_ui\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class ContentUpdateSelectForm.
 *
 * @package Drupal\gathercontent\Form
 */
class ContentUpdateSelectForm extends ContentSelectForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gathercontent_content_update_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->store->set('nodes', array_filter($form_state->getValue('nodes')));
    $form_state->setRedirect('gathercontent_ui.update_confirm_form');
  }

}
