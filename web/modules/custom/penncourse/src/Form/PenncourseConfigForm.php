<?php

namespace Drupal\penncourse\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class PenncourseConfigForm.
 */
class PenncourseConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'penncourse.penncourseconfig',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'penncourse_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('penncourse.penncourseconfig');
    $form['penncourse_subject_areas'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject areas'),
      '#description' => $this->t('Enter the subject areas from which you would like to display course data on your site. Use the alphanumeric subject code. List multiple subject areas separated by a space (eg, &quot;DTCH GRMN SCND YDSH&quot;).'),
      '#maxlength' => 256,
      '#size' => 64,
      '#default_value' => $config->get('penncourse_subject_areas'),
    ];
    $form['penncourse_authorization_bearer'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Application ID'),
      '#description' => $this->t('Enter the application ID for this service access account. <a href="@request-credentials">Request credentials here.</a>', array(
        '@request-credentials' => 'https://secure.www.upenn.edu/computing/da/webloginportal/eforms/index.html?content=kew/EDocLite?edlName=openDataRequestForm&userAction=initiate',
      )),
      '#maxlength' => 200,
      '#size' => 64,
      '#default_value' => $config->get('penncourse_authorization_bearer'),
    ];
    $form['penncourse_authorization_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Authorization token'),
      '#description' => $this->t('Enter the authorization token for this service access account. <a href="@request-credentials">Request credentials here.</a>', array(
        '@request-credentials' => 'https://secure.www.upenn.edu/computing/da/webloginportal/eforms/index.html?content=kew/EDocLite?edlName=openDataRequestForm&userAction=initiate',
      )),
      '#maxlength' => 200,
      '#size' => 64,
      '#default_value' => $config->get('penncourse_authorization_token'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('penncourse.penncourseconfig')
      ->set('penncourse_subject_areas', $form_state->getValue('penncourse_subject_areas'))
      ->set('penncourse_authorization_bearer', $form_state->getValue('penncourse_authorization_bearer'))
      ->set('penncourse_authorization_token', $form_state->getValue('penncourse_authorization_token'))
      ->save();
  }

}
