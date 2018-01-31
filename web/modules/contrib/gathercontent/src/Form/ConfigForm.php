<?php

namespace Drupal\gathercontent\Form;

use Cheppers\GatherContent\GatherContentClientInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ConfigForm.
 *
 * @package Drupal\gathercontent\Form
 */
class ConfigForm extends ConfigFormBase {

  /**
   * GatherContent client.
   *
   * @var \Drupal\gathercontent\DrupalGatherContentClient
   */
  protected $client;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, GatherContentClientInterface $client) {
    parent::__construct($config_factory);
    $this->client = $client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('gathercontent.client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('gathercontent.settings');
    $form['gathercontent_username'] = [
      '#type' => 'email',
      '#title' => $this->t('GatherContent User Email Address'),
      '#description' => $this->t('This is the email address you use to login to GatherContent. Your permissions will determine what accounts, projects and content is available.'),
      '#default_value' => $config->get('gathercontent_username'),
      '#required' => TRUE,
    ];
    $form['gathercontent_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('GatherContent API key'),
      '#description' => Link::fromTextAndUrl($this->t('Click to find out where you can generate your API Key'), Url::fromUri('https://gathercontent.com/developers/authentication/')),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('gathercontent_api_key'),
      '#required' => TRUE,
    ];

    $form['actions']['#type'] = 'actions';

    if (!$form_state->isSubmitted()) {
      $account = $config->get('gathercontent_account');
      if (!empty($account)) {
        $account = unserialize($account);
        $account = array_pop($account);
        $form['current_account'] = [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#value' => $this->t('Current account is <strong>@account</strong>.', ['@account' => $account]),
        ];
      }
    }

    if ($form_state->isSubmitted()) {
      /** @var \Cheppers\GatherContent\DataTypes\Account[] $data */
      $data = $this->client->accountsGet();
      $accounts = [];

      if (!is_null($data)) {
        foreach ($data as $account) {
          $accounts[$account->id] = $account->name;
        }

        $form['account'] = [
          '#type' => 'select',
          '#options' => $accounts,
          '#title' => $this->t('Select GatherContent Account'),
          '#required' => TRUE,
          '#description' => $this->t('Multiple accounts will be listed if the GatherContent
       user has more than one account. Please select the account you want to
       import and update content from.'),
        ];
      }
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => (is_null($data)) ? $this->t('Verify') : $this->t('Save'),
        '#button_type' => 'primary',
      ];
    }
    else {
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => (!empty($account) ? $this->t('Change GatherContent Account') : $this->t('Verify')),
        '#button_type' => 'primary',
      ];
    }

    if (!empty($account)) {
      $form['actions']['reset'] = [
        '#type' => 'submit',
        '#value' => $this->t('Reset credentials'),
      ];
    }

    // By default, render the form using theme_system_config_form().
    $form['#theme'] = 'system_config_form';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    if ($triggering_element['#id'] === 'edit-submit') {
      if (!$form_state->hasValue('account')) {
        $this->config('gathercontent.settings')
          ->set('gathercontent_username', $form_state->getValue('gathercontent_username'))
          ->set('gathercontent_api_key', $form_state->getValue('gathercontent_api_key'))
          ->save();
        $this->client->setCredentials();
        $form_state->setSubmitted()->setRebuild();
      }
      else {
        $submitted_account_id = $form_state->getValue('account');

        /** @var \Cheppers\GatherContent\DataTypes\Account[] $data */
        $data = $this->client->accountsGet();
        foreach ($data as $account) {
          if ($account->id == $submitted_account_id) {
            $account_name = $account->name;
            $this->config('gathercontent.settings')->set('gathercontent_account', serialize([$submitted_account_id => $account_name]))->save();
            drupal_set_message(t("Credentials and project were saved."));
            $this->config('gathercontent.settings')->set('gathercontent_urlkey', $account->slug)->save();
            break;
          }
        }
      }
    }
    elseif ($triggering_element['#id'] === 'edit-reset') {
      $this->config('gathercontent.settings')->clear('gathercontent_username')->save();
      $this->config('gathercontent.settings')->clear('gathercontent_api_key')->save();
      $this->config('gathercontent.settings')->clear('gathercontent_account')->save();
      $this->config('gathercontent.settings')->clear('gathercontent_urlkey')->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'gathercontent.settings',
    ];
  }

}
