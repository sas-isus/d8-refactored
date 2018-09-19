<?php

/**
 * @file
 * Contains \Drupal\easy_social\LinkedInSettingsForm.
 */

namespace Drupal\easy_social\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Plugin\Context\ContextInterface;
use Drupal\Core\Extension\ModuleHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;


/**
 * Configure user settings for this site.
 */
class LinkedInSettingsForm extends ConfigFormBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * Constructs a \Drupal\user\AccountSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Plugin\Context\ContextInterface $context
   *   The configuration context.
   * @param \Drupal\Core\Extension\ModuleHandler $module_handler
   *   The module handler.
   */
  public function __construct(ConfigFactory $config_factory, ModuleHandler $module_handler) {
    parent::__construct($config_factory);
    $this->moduleHandler = $module_handler;
  }

  /**
   * Implements \Drupal\Core\ControllerInterface::create().
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler')
    );
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::getFormID().
   */
  public function getFormID() {
    return 'easy_social_linkedin';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['easy_social.linkedin'];
  }


  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   */
  public function buildForm(array $form, FormStateInterface $form_state, $type = 'new') {
    $config = $this->config('easy_social.linkedin');

    $form['help'] = array(
      '#markup' => t('For more information, please check out the official @linkedin share widget <a href="@url" target="_blank">documentation</a>', array(
        '@linkedin' => t('LinkedIn'),
        '@url' => 'http://developer.linkedin.com/plugins/share-plugin-generator'
      )),
      '#weight' => -99,
    );

    $form['counter'] = array(
      '#type' => 'select',
      '#title' => t('Counter'),
      '#default_value' => $config->get('counter'),
      '#options' => array(
        'top' => t('Vertical'),
        'right' => t('Horizontal'),
        'none' => t('No Count')
      ),
    );

    $form['lang'] = array(
      '#type' => 'select',
      '#title' => t('Language'),
      '#description' => t('Content Default will use either the current content\'s or Drupal\'s language'),
      '#default_value' => $config->get('lang'),
      '#options' => array(
        // @TODO map linkedin's language codes to drupal's.
        '' => t('Content Default'),
        'en_US' => t('English'),
        'fr_FR' => t('French'),
        'es_ES' => t('Spanish'),
        'ru_RU' => t('Russian'),
        'de_DE' => t('German'),
        'it_IT' => t('Italian'),
        'pt_BR' => t('Portuguese'),
        'ro_RO' => t('Romanian'),
        'tr_TR' => t('Turkish'),
        'ja_JP' => t('Japanese'),
        'in_ID' => t('Indonesian'),
        'ms_MY' => t('Malay'),
        'ko_KR' => t('Korean'),
        'sv_SE' => t('Swedish'),
        'cs_CZ' => t('Czech'),
        'nl_NL' => t('Dutch'),
        'pl_PL' => t('Polish'),
        'no_NO' => t('Norwegian'),
        'da_DK' => t('Danish'),
      ),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::submitForm().
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->config('easy_social.linkedin');

    $config
      ->set('counter', $form_state->getValue('counter'))
      ->set('lang', $form_state->getValue('lang'))
      ->save();
  }

}
