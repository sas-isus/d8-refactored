<?php

namespace Drupal\permissions_by_term\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;


class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'permissions_by_term_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'permissions_by_term.settings'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $description = <<<EOT
By default users are granted access content, as long they have access to a <strong>single</strong>
related taxonomy term. If the single term restriction option is checked, they must
have access to <strong>all</strong> related taxonomy terms to access an node.
EOT;

    $form['single_term_restriction'] = [
      '#type' => 'checkbox',
      '#title' => t('Single Term Restriction'),
      '#description' => t($description),
      '#default_value' => \Drupal::config('permissions_by_term.settings.single_term_restriction')->get('value'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::configFactory()
      ->getEditable('permissions_by_term.settings.single_term_restriction')
      ->set('value', $form_state->getValue('single_term_restriction'))
      ->save();

    node_access_rebuild(true);

    parent::submitForm($form, $form_state);
  }

}
