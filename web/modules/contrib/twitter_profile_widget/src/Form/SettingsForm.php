<?php

namespace Drupal\twitter_profile_widget\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\twitter_profile_widget\Authorization;
use Drupal\Core\Cache\Cache;

/**
 * Class SettingsForm.
 *
 * @package Drupal\twitter_profile_widget\Form
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'twitter_widget_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'twitter_profile_widget.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('twitter_profile_widget.settings');

    $form['twitter_widget_description'] = [
      '#markup' => $this->t('Assign the Twitter App for this site. To register a new App, go to the <a href=":url">Twitter App page</a>.', [':url' => 'https://developer.twitter.com/en/apps/']),
    ];

    $connection = Authorization::getToken($config->get('twitter_widget_key'), $config->get('twitter_widget_secret'));
    $status = $connection ? 'Connected' : 'Not connected';
    $form['twitter_widget_token'] = [
      '#type'   => 'markup',
      '#markup' => $this->t('<h3><strong>Connection status: </strong> :status</h3>', [':status' => $status]),
    ];
    $form['twitter_widget_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Twitter App Key'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('twitter_widget_key'),
      '#required' => TRUE,
    ];
    $form['twitter_widget_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Twitter App Secret'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('twitter_widget_secret'),
      '#required' => TRUE,
    ];
    $form['twitter_widget_cache_time'] = [
      '#type' => 'number',
      '#title' => $this->t('Refresh Interval (seconds)'),
      '#default_value' => $config->get('twitter_widget_cache_time'),
      '#description' => $this->t('The Twitter <a href=":url">rate limiting policy</a> requires you limit how frequently you pull new tweets. The general rule: do not pull more frequently (in minutes) than the number of widgets should exceed the number of individual widgets on the site (e.g., if there are 5 widgets, the cache lifetime should be at least 300 seconds).', [':url' => 'https://dev.twitter.com/rest/public/rate-limits']),
    ];
    $form['expire_internal_cache'] = [
      '#type' => 'checkbox',
      '#title' => 'Correctly expire page cache',
      '#description' => $this->t('Use this if you have the Internal Page Cache enabled and are not using a memory-based cache such as Varnish. By default, the internal (anonymous) page cache will never expire, regardless of what you have set on the <a href=":url">Performance configuration page</a>. Checking this box will set the internal page cache to expire based on the "Page cache maximum age" setting. This change <em>only</em> applies to pages that include Twitter widgets.', [':url' => '/admin/config/development/performance']),
      '#default_value' => $config->get('expire_internal_cache'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Invalidate cached Twitter data for widget views.
    Cache::invalidateTags(['twitter_widget_view']);

    $values = $form_state->getValues();
    Authorization::getToken($values['twitter_widget_key'], $values['twitter_widget_secret']);
    $this->config('twitter_profile_widget.settings')
      ->set('expire_internal_cache', $values['expire_internal_cache'])
      ->set('twitter_widget_key', $values['twitter_widget_key'])
      ->set('twitter_widget_secret', $values['twitter_widget_secret'])
      ->set('twitter_widget_cache_time', $values['twitter_widget_cache_time'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
