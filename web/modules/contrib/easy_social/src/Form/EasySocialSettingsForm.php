<?php

/**
 * @file
 * Contains \Drupal\easy_social\Form\EasySocialSettingsForm.
 */

namespace Drupal\easy_social\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Plugin\Context\ContextInterface;
use Drupal\Core\Extension\ModuleHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;


/**
 * Configure Easy Social global settings for this site.
 */
class EasySocialSettingsForm extends ConfigFormBase {
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
    return 'easy_social_settings';
  }


  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['easy_social.settings'];
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   */
  public function buildForm(array $form, FormStateInterface $form_state, $type = 'new') {
    $config = $this->config('easy_social.settings');

    if ($widgets = easy_social_get_widgets()) {
      $widget_options = array();

      foreach ($widgets as $machine_name => $widget) {
        $widget_options[$machine_name] = $widget['name'];
      }

      $form['widgets'] = array(
        '#type' => 'checkboxes',
        '#title' => t('Enabled widgets'),
        // @TODO decide whether we're going to enable overrides for specific entities and, if so, update the description.
        '#description' => t('Select the social sharing widgets you would like to enable globally. This setting can be overriden per block and entity type.'),
        '#default_value' => (array) $config->get('global.widgets'),
        '#options' => $widget_options,
      );

      $form['advanced'] = array(
        '#type' => 'fieldset',
        '#title' => t('Advanced settings'),
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
      );

      $form['advanced']['async'] = array(
        '#type' => 'checkbox',
        '#title' => t('Load javascript asynchronously'),
        '#description' => t('This is recommended for performance purposes.'),
        '#default_value' => $config->get('global.async'),
      );
    }
    else {
      $form['widgets_empty'] = array(
        '#prefix' => '<div class="empty">',
        '#suffix' => '</div>',
        '#markup' => t('There are no widgets defined.'),
      );
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::submitForm().
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->config('easy_social.settings');

    $config
      ->set('global.widgets', $form_state->getValue('widgets'))
      ->set('global.async', $form_state->getValue('async'))
      ->save();
  }

}
