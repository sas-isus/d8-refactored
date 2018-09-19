<?php

/**
 * @file
 * Contains \Drupal\easy_social\TwitterSettingsForm.
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
class TwitterSettingsForm extends ConfigFormBase {

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
    return 'easy_social_twitter';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['easy_social.twitter'];
  }


  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   */
  public function buildForm(array $form, FormStateInterface $form_state, $type = 'new') {
    $config = $this->config('easy_social.twitter');

    $form['help'] = array(
      '#markup' => t('For more information, please check out the official @twitter share widget <a href="@url" target="_blank">documentation</a>', array(
        '@twitter' => t('Twitter'),
        '@url' => 'https://dev.twitter.com/docs/tweet-button'
      )),
      '#weight' => -99,
    );

    $form['via'] = array(
      '#type' => 'textfield',
      '#title' => t('Via'),
      '#description' => t('Screen name of the user to attribute the Tweet to.'),
      '#default_value' => $config->get('via'),
    );

    $form['related'] = array(
      '#type' => 'textfield',
      '#title' => t('Recommend'),
      '#description' => t('Related accounts. You can add your own summary of a related user by adding some text after their screen name, separated using a colon. You can also add multiple accounts, separating them by commas.<br>For example: "alexweber15:Alex Weber,lnunesbr:Leandro Nunes"'),
      '#default_value' => $config->get('related'),
    );

    $form['count'] = array(
      '#type' => 'select',
      '#title' => t('Count box position'),
      '#options' => array(
        'none' => t('None'),
        'horizontal' => t('Horizontal'),
        'vertical' => t('Vertical'),
      ),
      '#default_value' => $config->get('count'),
    );

    $form['hashtags'] = array(
      '#type' => 'textfield',
      '#title' => t('Hashtags'),
      '#description' => t('Comma separated hashtags appended to tweet text.'),
      '#default_value' => $config->get('hashtags'),
    );

    $form['size'] = array(
      '#type' => 'checkbox',
      '#title' => t('Large button'),
      '#description' => t('Please note that there is no large vertical widget. Checking this option with the vertical count box position will result in large horizontal button with no count.'),
      '#default_value' => $config->get('size'),
    );

    $form['dnt'] = array(
      '#type' => 'checkbox',
      '#title' => t('Opt-out of tailoring Twitter [<a href="@url" target="_blank">?</a>]', array('@url' => 'https://support.twitter.com/articles/20169421')),
      '#default_value' => $config->get('dnt'),
    );

    $form['lang'] = array(
      '#type' => 'select',
      '#title' => t('Language'),
      '#description' => t('Content Default will use either the current content\'s or Drupal\'s language'),
      '#default_value' => $config->get('lang'),
      '#options' => array(
        '' => t('Content Default'),
        // @TODO map twitter's language codes to drupal's.
        'en' => t('English'),
        'fr' => t('French'),
        'ar' => t('Arabic'),
        'ja' => t('Japanese'),
        'es' => t('Spanish'),
        'de' => t('German'),
        'it' => t('Italian'),
        'id' => t('Indonesian'),
        'pt' => t('Portuguese'),
        'ko' => t('Korean'),
        'tr' => t('Turkish'),
        'ru' => t('Russian'),
        'nl' => t('Dutch'),
        'fil' => t('Filipino'),
        'msa' => t('Malay'),
        'zh-tw' => t('Traditional Chinese'),
        'zh-cn' => t('Simplified Chinese'),
        'hi' => t('Hindi'),
        'no' => t('Norwegian'),
        'sv' => t('Swedish'),
        'fi' => t('Finnish'),
        'da' => t('Danish'),
        'pl' => t('Polish'),
        'hu' => t('Hungarian'),
        'fa' => t('Farsi'),
        'he' => t('Hebrew'),
        'ur' => t('Urdu'),
        'th' => t('Thai'),
        'uk' => t('Ukrainian'),
        'ca' => t('Catalan'),
        'el' => t('Greek'),
        'eu' => t('Basque'),
        'cs' => t('Czech'),
        'xx-lc' => t('Lolcat'),
        'gl' => t('Galician'),
        'ro' => t('Romanian'),
      ),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::submitForm().
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->config('easy_social.twitter');

    $config
      ->set('via', $form_state->getValue('via'))
      ->set('related', $form_state->getValue('related'))
      ->set('count', $form_state->getValue('count'))
      ->set('hashtags', $form_state->getValue('hashtags'))
      ->set('size', $form_state->getValue('size'))
      ->set('dnt', $form_state->getValue('dnt'))
      ->set('lang', $form_state->getValue('lang'))
      ->save();
  }

}
