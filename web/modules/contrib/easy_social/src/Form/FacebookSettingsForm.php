<?php

/**
 * @file
 * Contains \Drupal\easy_social\FacebookSettingsForm.
 */

namespace Drupal\easy_social\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactory;
use \Drupal\Core\Plugin\Context\ContextInterface;
use Drupal\Core\Extension\ModuleHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure user settings for this site.
 */
class FacebookSettingsForm extends ConfigFormBase {

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
    return 'easy_social_facebook';
  }


  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['easy_social.facebook'];
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   */
  public function buildForm(array $form, FormStateInterface $form_state, $type = 'new') {
    $config = $this->config('easy_social.facebook');

    $form['help'] = array(
      '#markup' => t('For more information, please check out the official @facebook share widget <a href="@url" target="_blank">documentation</a>', array(
        '@facebook' => t('Facebook'),
        '@url' => 'https://developers.facebook.com/docs/reference/plugins/like'
      )),
      '#weight' => -99,
    );

    $form['send'] = array(
      '#type' => 'checkbox',
      '#title' => t('Send Button'),
      '#description' => t('Include a Send button.'),
      '#default_value' => $config->get('send'),
    );

    $form['share'] = array(
      '#type' => 'checkbox',
      '#title' => t('Add Share Button'),
      '#description' => t('Include a Share button.'),
      '#default_value' => $config->get('share'),
    );

    $form['layout'] = array(
      '#type' => 'select',
      '#title' => t('Layout Style'),
      '#description' => t('Determines the size and amount of social context next to the button.'),
      '#default_value' => $config->get('layout'),
      '#options' => array(
        'standard' => t('standard'),
        'button_count' => t('button_count'),
        'box_count' => t('box_count'),
      ),
    );

    $form['width'] = array(
      '#type' => 'textfield',
      '#title' => t('Width'),
      '#description' => t('The width of the plugin, in pixels.'),
      '#default_value' => $config->get('width'),
      '#size' => 10,
    );

    $form['show_faces'] = array(
      '#type' => 'checkbox',
      '#title' => t('Show Faces'),
      '#description' => t('Show profile pictures when two or more friends like this.'),
      '#default_value' => $config->get('show_faces'),
    );

    $form['font'] = array(
      '#type' => 'select',
      '#title' => t('Font'),
      '#description' => t('The font of the plugin.'),
      '#default_value' => $config->get('font'),
      '#options' => array(
        '' => t('Default'),
        'arial' => t('arial'),
        'lucida grande' => t('lucida grande'),
        'segoe ui' => t('segoe ui'),
        'tahoma' => t('tahoma'),
        'trebuchet ms' => t('trebuchet ms'),
        'verdana' => t('verdana'),
      ),
    );

    $form['colorscheme'] = array(
      '#type' => 'select',
      '#title' => t('Color Scheme'),
      '#description' => t('The color scheme of the plugin.'),
      '#default_value' => $config->get('colorscheme'),
      '#options' => array(
        'light' => t('light'),
        'dark' => t('dark'),
      ),
    );

    $form['action'] = array(
      '#type' => 'select',
      '#title' => t('Verb to display'),
      '#description' => t('The verb to display in the button.'),
      '#default_value' => $config->get('action'),
      '#options' => array(
        'like' => t('like'),
        'recommend' => t('recommend'),
      ),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::submitForm().
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $config = $this->config('easy_social.facebook');

    $config
      ->set('share', $form_state->getValue('share'))
      ->set('send', $form_state->getValue('send'))
      ->set('layout', $form_state->getValue('layout'))
      ->set('width', $form_state->getValue('width'))
      ->set('show_faces', $form_state->getValue('show_faces'))
      ->set('font', $form_state->getValue('font'))
      ->set('colorscheme', $form_state->getValue('colorscheme'))
      ->set('action', $form_state->getValue('action'))
      ->save();
  }

}
