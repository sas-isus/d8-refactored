<?php

namespace Drupal\lightning_media\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * The settings form for controlling Lightning Media's behavior.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['lightning_media.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lightning_media_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['choose_display'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow users to choose how to display embedded media'),
      '#default_value' => $this->config('lightning_media.settings')->get('entity_embed.choose_display'),
    ];
    $form['override_widget'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Override the default widget of media reference fields'),
      '#default_value' => $this->config('lightning_media.settings')->get('entity_browser.override_widget'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('lightning_media.settings')
      ->set('entity_embed.choose_display', (bool) $form_state->getValue('choose_display'))
      ->set('entity_browser.override_widget', (bool) $form_state->getValue('override_widget'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
