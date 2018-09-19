<?php

/**
 * @file
 * Contains \Drupal\easy_social\GooglePlusSettingsForm.
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
class GooglePlusSettingsForm extends ConfigFormBase {

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
    return 'easy_social_googleplus';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['easy_social.googleplus'];
  }


  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   */
  public function buildForm(array $form, FormStateInterface $form_state, $type = 'new') {
    $config = $this->config('easy_social.googleplus');

    $form['help'] = array(
      '#markup' => t('For more information, please check out the official @googleplus share widget <a href="@url" target="_blank">documentation</a>', array(
        '@googleplus' => t('Google+'),
        '@url' => 'https://developers.google.com/+/web/+1button'
      )),
      '#weight' => -99,
    );

    $form['size'] = array(
      '#type' => 'select',
      '#title' => t('Size'),
      '#options' => array(
        'small' => t('Small'),
        'medium' => t('Medium'),
        'large' => t('Large'),
      ),
      '#default_value' => $config->get('size'),
    );

    $form['annotation'] = array(
      '#type' => 'select',
      '#title' => t('Annotation'),
      '#options' => array(
        'inline' => t('Inline'),
        'bubble' => t('Bubble'),
        'vertical-bubble' => t('Vertical Bubble'),
        'none' => t('None'),
      ),
      '#default_value' => $config->get('annotation'),
    );

    $form['width'] = array(
      '#type' => 'textfield',
      '#title' => t('Width, valid only for inline (minimum 120)'),
      '#default_value' => $config->get('width'),
      '#size' => 10,
    );

    $form['lang'] = array(
      '#type' => 'select',
      '#title' => t('Language'),
      '#description' => t('Content Default will use either the current content\'s or Drupal\'s language'),
      '#default_value' => $config->get('lang'),
      '#options' => array(
        '' => t('Content Default'),
        // @TODO add google's language codes
        // @TODO map google's language codes to drupal's.
      ),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::validateForm().
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // @TODO validate $form_state['values']['width'] is numeric.
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::submitForm().
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->config('easy_social.googleplus');

    $config
      ->set('size', $form_state->getValue('size'))
      ->set('annotation', $form_state->getValue('annotation'))
      ->set('width', $form_state->getValue('width'))
      ->set('lang', $form_state->getValue('lang'))
      ->save();
  }

}
