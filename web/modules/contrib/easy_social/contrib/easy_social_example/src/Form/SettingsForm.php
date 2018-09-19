<?php

/**
 * @file
 * Contains \Drupal\easy_social_example\SettingsForm.
 */

namespace Drupal\easy_social_example;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactory;
use \Drupal\Core\Plugin\Context\ContextInterface;
use Drupal\Core\Extension\ModuleHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure user settings for this site.
 */
class SettingsForm extends ConfigFormBase {

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
  public function __construct(ConfigFactory $config_factory, ContextInterface $context, ModuleHandler $module_handler) {
    parent::__construct($config_factory, $context);
    $this->moduleHandler = $module_handler;
  }

  /**
   * Implements \Drupal\Core\ControllerInterface::create().
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('config.context.free'),
      $container->get('module_handler')
    );
  }


  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['easy_social_example.settings'];
  }


  /**
   * Implements \Drupal\Core\Form\FormInterface::getFormID().
   */
  public function getFormID() {
    return 'easy_social_example_settings';
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->get('easy_social.example');

    $form['help'] = array(
      '#markup' => t('For more information, please check out the official @example share widget <a href="@url" target="_blank">documentation</a>', array('@example' => t('Example'), '@url' => '#')),
      '#weight' => -99,
    );

    $form['size'] = array(
      '#type' => 'checkbox',
      '#title' => t('Large button'),
      '#default_value' => $config->get('size'),
    );

    $form['foo'] = array(
      '#type' => 'textfield',
      '#title' => t('Foo'),
      '#default_value' => $config->get('foo'),
    );

    $form['baz'] = array(
      '#type' => 'checkbox',
      '#title' => t('Baz'),
      '#default_value' => $config->get('baz'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::submitForm().
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->configFactory->get('easy_social.example')
      ->set('size', $form_state['values']['size'])
      ->set('foo', $form_state['values']['foo'])
      ->set('baz', $form_state['values']['baz'])
      ->save();
  }

}
